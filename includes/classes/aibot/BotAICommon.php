<?php
declare(strict_types=1);

abstract class BotAICommon
{
    protected const DEBUG = true;

    protected const MIN_HANGAR     = 4;
    protected const MIN_COMBUSTION = 3;
    protected const MIN_SPY_TECH   = 2;

    protected const MIN_SONDEN = 1;

    protected const SHIP_SMALL_CARGO   = 202;
    protected const SHIP_LARGE_CARGO   = 203;
    protected const SHIP_LIGHT_FIGHTER = 204;
    protected const SHIP_HEAVY_FIGHTER = 205;
    protected const SHIP_CRUISER       = 206;
    protected const SHIP_BATTLESHIP    = 207;
    protected const SHIP_SPY           = 210;
    protected const SHIP_DESTROYER     = 213;
    protected const SHIP_BATTLECRUISER = 215;

    protected const EXPEDITION_SHIPS  = 4;
    protected const EXPEDITION_PLANET = 16;

    protected const MIN_COMBAT_SHIPS    = 5;
    protected const MAX_ATTACK_DEFENSE  = 50;
    protected const MIN_ATTACK_COOLDOWN = 7200;
    protected const MAX_ATTACK_COOLDOWN = 21600;
    protected const MAX_ATTACK_RANGE    = 100;
    protected const INACTIVE_AFTER      = 604800;

    protected const MIN_ATTACK_LIGHT_FIGHTERS        = 20;
    protected const ATTACK_BUILD_BATCH_LIGHT_FIGHTER = 10;

    protected const ATTACK_USE_PERCENT_LF = 0.35;
    protected const ATTACK_USE_PERCENT_HF = 0.35;
    protected const ATTACK_USE_PERCENT_CR = 0.40;
    protected const ATTACK_USE_PERCENT_BS = 0.45;
    protected const ATTACK_USE_PERCENT_DS = 0.50;
    protected const ATTACK_USE_PERCENT_BC = 0.45;

    protected const ATTACK_HOME_RESERVE_LF = 20;
    protected const ATTACK_HOME_RESERVE_HF = 6;
    protected const ATTACK_HOME_RESERVE_CR = 3;
    protected const ATTACK_HOME_RESERVE_BS = 2;
    protected const ATTACK_HOME_RESERVE_DS = 1;
    protected const ATTACK_HOME_RESERVE_BC = 1;

    protected const ATTACK_MAX_SMALL_CARGO = 20;
    protected const ATTACK_MAX_LARGE_CARGO = 12;

    protected static function log(string $message, array $data = []): void
{
    $dir = ROOT_PATH . 'includes/ai_log/';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Klassenname automatisch erkennen (Spy / Attack / Expedition)
    $class = static::class;

    $file = match ($class) {
        'BotAISpy'         => 'bot_spy.log',
        'BotAIExpedition'  => 'bot_expedition.log',
        'BotAIAttack'      => 'bot_attack.log',
        default            => 'bot_general.log',
    };

    $logData = [
        'time'      => time(),
        'datetime'  => date('Y-m-d H:i:s'),
        'class'     => $class,
        'message'   => $message,
        'data'      => $data,
    ];

    file_put_contents(
        $dir . $file,
        json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND
    );
}

    protected static function normalizeUserFactors(array $USER): array
    {
        if (!isset($USER['factor']) || !is_array($USER['factor'])) {
            $USER['factor'] = [];
        }

        if (!isset($USER['factor']['FleetSlots'])) {
            $USER['factor']['FleetSlots'] = 0;
        }

        if (!isset($USER['factor']['SpeedFleet'])) {
            $USER['factor']['SpeedFleet'] = 0;
        }

        if (!isset($USER['factor']['FlyTime'])) {
            $USER['factor']['FlyTime'] = 0;
        }

        return $USER;
    }

    protected static function queueShip(int $planetId, int $shipId, int $amount): void
    {
        if ($amount <= 0) {
            static::log('QUEUE skip amount <= 0', [
                'planetId' => $planetId,
                'shipId'   => $shipId,
                'amount'   => $amount,
            ]);
            return;
        }

        $db = Database::get();

        $planet = $db->selectSingle(
            'SELECT b_hangar_id FROM %%PLANETS%% WHERE id = :id;',
            [':id' => $planetId]
        );

        $queue = [];

        if (!empty($planet['b_hangar_id'])) {
            $unserialized = @unserialize($planet['b_hangar_id']);
            if (is_array($unserialized)) {
                $queue = $unserialized;
            }
        }

        $found = false;

        foreach ($queue as $index => $entry) {
            if (is_array($entry) && isset($entry[0], $entry[1]) && (int)$entry[0] === $shipId) {
                $queue[$index][1] = (float)$entry[1] + $amount;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $queue[] = [$shipId, (float)$amount];
        }

        $db->update(
            'UPDATE %%PLANETS%% SET b_hangar_id = :queue WHERE id = :id;',
            [
                ':queue' => serialize($queue),
                ':id'    => $planetId,
            ]
        );

        static::log('QUEUE updated', [
            'planetId' => $planetId,
            'shipId'   => $shipId,
            'amount'   => $amount,
            'queue'    => $queue,
        ]);
    }

    protected static function getQueuedAmount(int $planetId, int $shipId): int
    {
        $planet = Database::get()->selectSingle(
            'SELECT b_hangar_id FROM %%PLANETS%% WHERE id = :id;',
            [':id' => $planetId]
        );

        if (empty($planet['b_hangar_id'])) {
            return 0;
        }

        $queue = @unserialize($planet['b_hangar_id']);
        if (!is_array($queue)) {
            return 0;
        }

        $amount = 0;

        foreach ($queue as $entry) {
            if (is_array($entry) && isset($entry[0], $entry[1]) && (int)$entry[0] === $shipId) {
                $amount += (int)$entry[1];
            }
        }

        static::log('QUEUE amount', [
            'planetId' => $planetId,
            'shipId'   => $shipId,
            'amount'   => $amount,
        ]);

        return $amount;
    }

    protected static function reloadBotUser(int $botId): ?array
    {
        $user = Database::get()->selectSingle(
            'SELECT * FROM %%USERS%% WHERE id = :id AND is_bot = 1;',
            [':id' => $botId]
        );

        return is_array($user) ? static::normalizeUserFactors($user) : null;
    }

    protected static function reloadPlanet(int $planetId): ?array
    {
        $planet = Database::get()->selectSingle(
            'SELECT * FROM %%PLANETS%% WHERE id = :id LIMIT 1;',
            [':id' => $planetId]
        );

        return is_array($planet) ? $planet : null;
    }
}
