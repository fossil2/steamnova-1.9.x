<?php
declare(strict_types=1);

require_once ROOT_PATH . 'includes/classes/aibot/BotAICommon.php';
require_once ROOT_PATH . 'includes/classes/aibot/BotAISpy.php';
require_once ROOT_PATH . 'includes/classes/aibot/BotAIExpedition.php';
require_once ROOT_PATH . 'includes/classes/aibot/BotAIAttack.php';

class BotAIManager extends BotAICommon
{
    public static function run(int $botId): void
    {
        $USER = static::reloadBotUser($botId);

        static::log('RUN start', ['botId' => $botId]);

        if (!$USER) {
            static::log('RUN no bot user found', ['botId' => $botId]);
            return;
        }

        $PLANETS = Database::get()->select(
            'SELECT *
             FROM %%PLANETS%%
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY galaxy ASC, system ASC, planet ASC;',
            [':uid' => $botId]
        );

        if (empty($PLANETS) || !is_array($PLANETS)) {
            static::log('RUN no planets found', ['botId' => $botId]);
            return;
        }

        static::log('RUN planets loaded', [
            'userId'      => $USER['id'],
            'planetCount' => count($PLANETS),
        ]);

        foreach ($PLANETS as $PLANET) {
            static::log('RUN process planet', [
                'userId'   => $USER['id'],
                'planetId' => $PLANET['id'],
                'galaxy'   => $PLANET['galaxy'] ?? null,
                'system'   => $PLANET['system'] ?? null,
                'planet'   => $PLANET['planet'] ?? null,
            ]);

            BotAISpy::runPlanet($USER, $PLANET);
            BotAIExpedition::runPlanet($USER, $PLANET);
        }

        usort($PLANETS, static function (array $a, array $b): int {
            $combatA = BotAIAttack::getPlanetCombatShips($a);
            $combatB = BotAIAttack::getPlanetCombatShips($b);
            return $combatB <=> $combatA;
        });

        foreach ($PLANETS as $PLANET) {
            BotAIAttack::runPlanet($USER, $PLANET);

            $freshUser = static::reloadBotUser((int)$botId);
            if ($freshUser !== null) {
                $USER = $freshUser;
            }

            if (!empty($USER['bot_next_attack']) && $USER['bot_next_attack'] > TIMESTAMP) {
                break;
            }
        }

        static::log('RUN finished', [
            'userId'      => $USER['id'],
            'planetCount' => count($PLANETS),
        ]);
    }

    public static function runSpyOnly(int $botId): void
    {
        $USER = static::reloadBotUser($botId);
        if (!$USER) {
            return;
        }

        $PLANETS = Database::get()->select(
            'SELECT * FROM %%PLANETS%% WHERE id_owner = :uid AND planet_type = 1 ORDER BY galaxy ASC, system ASC, planet ASC;',
            [':uid' => $botId]
        );

        foreach ($PLANETS as $PLANET) {
            BotAISpy::runPlanet($USER, $PLANET);
        }
    }

    public static function runExpeditionOnly(int $botId): void
    {
        $USER = static::reloadBotUser($botId);
        if (!$USER) {
            return;
        }

        $PLANETS = Database::get()->select(
            'SELECT * FROM %%PLANETS%% WHERE id_owner = :uid AND planet_type = 1 ORDER BY galaxy ASC, system ASC, planet ASC;',
            [':uid' => $botId]
        );

        foreach ($PLANETS as $PLANET) {
            BotAIExpedition::runPlanet($USER, $PLANET);
        }
    }

    public static function runAttackOnly(int $botId): void
    {
        $USER = static::reloadBotUser($botId);
        if (!$USER) {
            return;
        }

        $PLANETS = Database::get()->select(
            'SELECT * FROM %%PLANETS%% WHERE id_owner = :uid AND planet_type = 1 ORDER BY galaxy ASC, system ASC, planet ASC;',
            [':uid' => $botId]
        );

        usort($PLANETS, static function (array $a, array $b): int {
            $combatA = BotAIAttack::getPlanetCombatShips($a);
            $combatB = BotAIAttack::getPlanetCombatShips($b);
            return $combatB <=> $combatA;
        });

        foreach ($PLANETS as $PLANET) {
            BotAIAttack::runPlanet($USER, $PLANET);

            $freshUser = static::reloadBotUser($botId);
            if ($freshUser !== null) {
                $USER = $freshUser;
            }

            if (!empty($USER['bot_next_attack']) && $USER['bot_next_attack'] > TIMESTAMP) {
                break;
            }
        }
    }
}
