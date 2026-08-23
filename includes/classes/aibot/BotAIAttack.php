<?php
declare(strict_types=1);

require_once ROOT_PATH . 'includes/classes/aibot/BotAICommon.php';

class BotAIAttack extends BotAICommon
{
    private const MIN_TARGET_POINTS = 5000;

    private const MAX_SPY_AGE = 43200; // 12 Stunden
    private const ATTACK_TARGET_LOCK_SECONDS = 21600; // 6 Stunden

    private const MIN_RESOURCES_INACTIVE = 40000;
    private const MIN_RESOURCES_ACTIVE   = 80000;

    public static function runPlanet(array $USER, array $PLANET): bool
    {
        if (isset($USER['bot_enable_attack']) && (int)$USER['bot_enable_attack'] !== 1) {
            static::log('ATTACK disabled by toggle', [
                'userId'   => $USER['id'] ?? null,
                'planetId' => $PLANET['id'] ?? null,
            ]);
            return false;
        }

        $USER = static::normalizeUserFactors($USER);

        static::log('ATTACK start', [
            'user'   => $USER['id'],
            'planet' => $PLANET['id'],
        ]);

        if (!empty($USER['bot_next_attack']) && $USER['bot_next_attack'] > TIMESTAMP) {
            static::log('ATTACK cooldown active', [
                'next' => $USER['bot_next_attack'],
                'now'  => TIMESTAMP,
            ]);
            return false;
        }

        require_once ROOT_PATH . 'includes/classes/class.FleetFunctions.php';

        $current = FleetFunctions::GetCurrentFleets($USER['id']);
        $max     = FleetFunctions::GetMaxFleetSlots($USER);

        static::log('ATTACK fleet slots', [
            'current' => $current,
            'max'     => $max,
        ]);

        if ($current >= $max) {
            static::log('ATTACK no free fleet slot');
            return false;
        }

        if (!static::ensureAttackShips($PLANET)) {
            static::log('ATTACK waiting for combat ships to finish', [
                'planetId' => $PLANET['id'],
            ]);
            return false;
        }

        $TARGET = static::getAttackTarget($USER, $PLANET);

        static::log('ATTACK target', is_array($TARGET) ? [
            'targetPlanetId' => $TARGET['id'] ?? null,
            'targetOwner'    => $TARGET['id_owner'] ?? null,
            'galaxy'         => $TARGET['galaxy'] ?? null,
            'system'         => $TARGET['system'] ?? null,
            'planet'         => $TARGET['planet'] ?? null,
            'defense_count'  => $TARGET['defense_count'] ?? null,
            'onlinetime'     => $TARGET['onlinetime'] ?? null,
            'total_points'   => $TARGET['total_points'] ?? null,
            'total_resources'=> $TARGET['total_resources'] ?? null,
            'last_spy_time'  => $TARGET['last_spy_time'] ?? null,
            'last_attack_time' => $TARGET['last_attack_time'] ?? null,
            'profile'        => is_array($TARGET) ? static::getTargetProfile($TARGET) : null,
        ] : []);

        if (!$TARGET) {
            static::log('ATTACK no target found', [
                'reason'   => 'no recent spy target with enough resources / low defense / unlocked',
                'userId'   => $USER['id'] ?? null,
                'planetId' => $PLANET['id'] ?? null,
            ]);
            return false;
        }

        if (!static::isTargetAttackable($TARGET)) {
            static::log('ATTACK target rejected by attackability rules', [
                'targetPlanetId' => $TARGET['id'] ?? null,
                'targetOwner'    => $TARGET['id_owner'] ?? null,
                'defense_count'  => $TARGET['defense_count'] ?? null,
                'total_points'   => $TARGET['total_points'] ?? null,
                'total_resources'=> $TARGET['total_resources'] ?? null,
                'profile'        => static::getTargetProfile($TARGET),
            ]);
            return false;
        }

        $fleetArray = static::buildAttackFleet($PLANET, $TARGET);
        static::log('ATTACK fleet built', $fleetArray);

        if (empty($fleetArray)) {
            static::log('ATTACK no fleet available');
            return false;
        }

        static::sendAttackFleet($USER, $PLANET, $TARGET, $fleetArray);
        static::registerAttackOnTarget((int)$USER['id'], (int)$TARGET['id']);

        Database::get()->update(
            'UPDATE %%USERS%% SET bot_next_attack = :t WHERE id = :id;',
            [
                ':t'  => TIMESTAMP + mt_rand(static::MIN_ATTACK_COOLDOWN, static::MAX_ATTACK_COOLDOWN),
                ':id' => $USER['id'],
            ]
        );

        static::log('ATTACK cooldown set', ['userId' => $USER['id']]);
        return true;
    }

    public static function getPlanetCombatShips(array $PLANET): int
    {
        $ships = static::getAttackShipSet($PLANET);

        return $ships['LF'] + $ships['HF'] + $ships['CR'] + $ships['BS'] + $ships['DS'] + $ships['BC'];
    }

    protected static function getSpyTableName(): string
    {
        $prefix = DB_PREFIX;
        if (substr($prefix, -1) !== '_') {
            $prefix .= '_';
        }

        return $prefix . 'spy_bot_targets';
    }

    protected static function registerAttackOnTarget(int $botId, int $targetPlanetId): void
    {
        $table = static::getSpyTableName();

        Database::get()->update(
            "UPDATE {$table}
             SET last_attack_time = :time
             WHERE bot_id = :botId
               AND target_planet_id = :targetPlanetId;",
            [
                ':time'           => TIMESTAMP,
                ':botId'          => $botId,
                ':targetPlanetId' => $targetPlanetId,
            ]
        );

        static::log('ATTACK target locked', [
            'botId'          => $botId,
            'targetPlanetId' => $targetPlanetId,
            'lockSeconds'    => static::ATTACK_TARGET_LOCK_SECONDS,
        ]);
    }

    protected static function ensureAttackShips(array $PLANET): bool
    {
        $hangar = (int)($PLANET['hangar'] ?? 0);
        if ($hangar < static::MIN_HANGAR) {
            static::log('ATTACK no attack ship build possible yet', [
                'planetId' => $PLANET['id'],
                'hangar'   => $hangar,
                'need'     => static::MIN_HANGAR,
            ]);
            return false;
        }

        $ships    = static::getAttackShipSet($PLANET);
        $haveLF   = $ships['LF'];
        $queuedLF = static::getQueuedAmount((int)$PLANET['id'], static::SHIP_LIGHT_FIGHTER);
        $totalLF  = $haveLF + $queuedLF;

        static::log('ATTACK ensure ships', [
            'planetId' => $PLANET['id'] ?? null,
            'haveLF'   => $haveLF,
            'queuedLF' => $queuedLF,
            'totalLF'  => $totalLF,
            'needLF'   => static::MIN_ATTACK_LIGHT_FIGHTERS,
        ]);

        if ($haveLF >= static::MIN_ATTACK_LIGHT_FIGHTERS) {
            return true;
        }

        if ($totalLF < static::MIN_ATTACK_LIGHT_FIGHTERS) {
            $missing = min(
                static::ATTACK_BUILD_BATCH_LIGHT_FIGHTER,
                static::MIN_ATTACK_LIGHT_FIGHTERS - $totalLF
            );

            if ($missing > 0) {
                static::queueShip((int)$PLANET['id'], static::SHIP_LIGHT_FIGHTER, $missing);

                static::log('ATTACK queued light fighters', [
                    'planetId' => $PLANET['id'],
                    'amount'   => $missing,
                ]);
            }
        } else {
            static::log('ATTACK ships still in production', [
                'planetId' => $PLANET['id'],
                'haveLF'   => $haveLF,
                'queuedLF' => $queuedLF,
            ]);
        }

        return false;
    }

    protected static function getAttackShipSet(array $PLANET): array
    {
        return [
            'LF'  => (int)($PLANET['light_hunter'] ?? 0),
            'HF'  => (int)($PLANET['heavy_hunter'] ?? 0),
            'CR'  => (int)($PLANET['crusher'] ?? 0),
            'BS'  => (int)($PLANET['battle_ship'] ?? 0),
            'DS'  => (int)($PLANET['destructor'] ?? 0),
            'BC'  => (int)($PLANET['battleship'] ?? 0),
            'SC'  => (int)($PLANET['small_ship_cargo'] ?? 0),
            'LC'  => (int)($PLANET['big_ship_cargo'] ?? 0),
            'SPY' => (int)($PLANET['spy_sonde'] ?? 0),
        ];
    }

    protected static function getAttackShipAmount(int $have, float $percent, int $reserve): int
    {
        if ($have <= $reserve) {
            return 0;
        }

        $usable = $have - $reserve;
        $send   = (int)floor($usable * $percent);

        if ($send < 1 && $usable > 0) {
            $send = 1;
        }

        return max(0, min($send, $usable));
    }

    protected static function isInactiveTarget(array $TARGET): bool
    {
        return (int)($TARGET['onlinetime'] ?? 0) < (TIMESTAMP - static::INACTIVE_AFTER);
    }

    protected static function hasEnoughResourcesForAttack(array $TARGET): bool
    {
        $resources = (int)($TARGET['total_resources'] ?? 0);
        $inactive  = static::isInactiveTarget($TARGET);

        if ($inactive) {
            return $resources >= static::MIN_RESOURCES_INACTIVE;
        }

        return $resources >= static::MIN_RESOURCES_ACTIVE;
    }

    protected static function getTargetProfile(array $TARGET): string
    {
        $inactive = static::isInactiveTarget($TARGET);
        $def      = (int)($TARGET['defense_count'] ?? 0);

        if ($inactive && $def <= 0) {
            return 'inactive_farm';
        }
        if ($inactive && $def <= 20) {
            return 'inactive_defended';
        }
        if (!$inactive && $def <= 10) {
            return 'active_weak';
        }

        return 'reject';
    }

    protected static function isTargetAttackable(array $TARGET): bool
    {
        $points   = (int)($TARGET['total_points'] ?? 0);
        $def      = (int)($TARGET['defense_count'] ?? 0);
        $profile  = static::getTargetProfile($TARGET);
        $inactive = static::isInactiveTarget($TARGET);

        // Aktive Noobs nicht angreifen
        if (!$inactive && $points < self::MIN_TARGET_POINTS) {
            return false;
        }

        // Inaktive dürfen auch unter 5000 Punkten angegriffen werden
        if ($profile === 'reject') {
            return false;
        }

        if (!in_array($profile, ['inactive_farm', 'inactive_defended', 'active_weak'], true)) {
            return false;
        }

        if ($profile === 'active_weak' && $def > 10) {
            return false;
        }

        if (!static::hasEnoughResourcesForAttack($TARGET)) {
            return false;
        }

        return true;
    }

    protected static function getDefenseFactor(?array $TARGET): float
    {
        if (!$TARGET) {
            return 0.0;
        }

        $profile = static::getTargetProfile($TARGET);

        switch ($profile) {
            case 'inactive_farm':
                return 1.00;

            case 'inactive_defended':
                return 0.85;

            case 'active_weak':
                return 0.70;

            default:
                return 0.0;
        }
    }

    protected static function getShipPowerWeights(): array
    {
        return [
            static::SHIP_LIGHT_FIGHTER => 1,
            static::SHIP_HEAVY_FIGHTER => 3,
            static::SHIP_CRUISER       => 6,
            static::SHIP_BATTLESHIP    => 10,
            static::SHIP_DESTROYER     => 16,
            static::SHIP_BATTLECRUISER => 14,
        ];
    }

    protected static function getFleetPower(array $fleetArray): int
    {
        $weights = static::getShipPowerWeights();
        $power   = 0;

        foreach ($fleetArray as $shipId => $amount) {
            $power += ((int)($weights[$shipId] ?? 0) * (int)$amount);
        }

        return $power;
    }

    protected static function getTargetRequiredPower(array $TARGET): int
    {
        $def        = (int)($TARGET['defense_count'] ?? 0);
        $profile    = static::getTargetProfile($TARGET);
        $base       = 0;
        $multiplier = 1.0;

        switch ($profile) {
            case 'inactive_farm':
                $base = 8;
                $multiplier = 2.0;
                break;

            case 'inactive_defended':
                $base = 14;
                $multiplier = 3.0;
                break;

            case 'active_weak':
                $base = 24;
                $multiplier = 4.0;
                break;

            default:
                return PHP_INT_MAX;
        }

        return (int)ceil($base + ($def * $multiplier));
    }

    protected static function buildAttackFleet(array $PLANET, ?array $TARGET = null): array
    {
        if (!$TARGET) {
            return [];
        }

        $profile = static::getTargetProfile($TARGET);
        if (!static::isTargetAttackable($TARGET)) {
            static::log('ATTACK build fleet reject target profile', [
                'planetId' => $PLANET['id'] ?? null,
                'profile'  => $profile,
            ]);
            return [];
        }

        $fleet = [];
        $ships = static::getAttackShipSet($PLANET);

        static::log('ATTACK ships detected', array_merge(
            [
                'planetId'  => $PLANET['id'] ?? null,
                'profile'   => $profile,
                'defFactor' => static::getDefenseFactor($TARGET),
            ],
            $ships
        ));

        $config = static::getAttackProfileConfig($TARGET);

        $usableLF = max(0, $ships['LF'] - $config['reserveLF']);
        $usableHF = max(0, $ships['HF'] - $config['reserveHF']);
        $usableCR = max(0, $ships['CR'] - $config['reserveCR']);
        $usableBS = max(0, $ships['BS'] - $config['reserveBS']);
        $usableDS = max(0, $ships['DS'] - $config['reserveDS']);
        $usableBC = max(0, $ships['BC'] - $config['reserveBC']);

        $sendLF = (int)floor($usableLF * $config['useLF']);
        $sendHF = (int)floor($usableHF * $config['useHF']);
        $sendCR = (int)floor($usableCR * $config['useCR']);
        $sendBS = (int)floor($usableBS * $config['useBS']);
        $sendDS = (int)floor($usableDS * $config['useDS']);
        $sendBC = (int)floor($usableBC * $config['useBC']);

        if ($usableLF > 0 && $sendLF < $config['minSendLF']) {
            $sendLF = min($usableLF, $config['minSendLF']);
        }

        if ($usableHF > 0 && $sendHF < $config['minSendHF']) {
            $sendHF = min($usableHF, $config['minSendHF']);
        }

        if ($usableCR > 0 && $sendCR < $config['minSendCR']) {
            $sendCR = min($usableCR, $config['minSendCR']);
        }

        if ($usableBS > 0 && $sendBS < $config['minSendBS']) {
            $sendBS = min($usableBS, $config['minSendBS']);
        }

        if ($usableDS > 0 && $sendDS < $config['minSendDS']) {
            $sendDS = min($usableDS, $config['minSendDS']);
        }

        if ($usableBC > 0 && $sendBC < $config['minSendBC']) {
            $sendBC = min($usableBC, $config['minSendBC']);
        }

        if ($sendLF > 0) {
            $fleet[static::SHIP_LIGHT_FIGHTER] = $sendLF;
        }
        if ($sendHF > 0) {
            $fleet[static::SHIP_HEAVY_FIGHTER] = $sendHF;
        }
        if ($sendCR > 0) {
            $fleet[static::SHIP_CRUISER] = $sendCR;
        }
        if ($sendBS > 0) {
            $fleet[static::SHIP_BATTLESHIP] = $sendBS;
        }
        if ($sendDS > 0) {
            $fleet[static::SHIP_DESTROYER] = $sendDS;
        }
        if ($sendBC > 0) {
            $fleet[static::SHIP_BATTLECRUISER] = $sendBC;
        }

        $combatShips = static::countCombatShipsInFleet($fleet);
        $fleetPower  = static::getFleetPower($fleet);
        $needPower   = static::getTargetRequiredPower($TARGET);

        static::log('ATTACK combat evaluation', [
            'planetId'     => $PLANET['id'] ?? null,
            'profile'      => $profile,
            'combatShips'  => $combatShips,
            'fleetPower'   => $fleetPower,
            'needPower'    => $needPower,
            'defenseCount' => (int)($TARGET['defense_count'] ?? 0),
        ]);

        if ($combatShips < static::MIN_COMBAT_SHIPS) {
            static::log('ATTACK reject fleet by min combat ships', [
                'planetId' => $PLANET['id'] ?? null,
                'count'    => $combatShips,
                'min'      => static::MIN_COMBAT_SHIPS,
            ]);
            return [];
        }

        if ($fleetPower < $needPower) {
            static::log('ATTACK reject fleet by power', [
                'planetId'   => $PLANET['id'] ?? null,
                'fleetPower' => $fleetPower,
                'needPower'  => $needPower,
                'profile'    => $profile,
            ]);
            return [];
        }

        $sendLC = min($ships['LC'], $config['maxLC']);
        $sendSC = min($ships['SC'], $config['maxSC']);

        if ($sendLC > 0) {
            $fleet[static::SHIP_LARGE_CARGO] = $sendLC;
        } elseif ($sendSC > 0) {
            $fleet[static::SHIP_SMALL_CARGO] = $sendSC;
        }

        static::log('ATTACK fleet composition', [
            'planetId' => $PLANET['id'] ?? null,
            'profile'  => $profile,
            'fleet'    => $fleet,
        ]);

        return $fleet;
    }

    protected static function getAttackProfileConfig(array $TARGET): array
    {
        $profile = static::getTargetProfile($TARGET);

        switch ($profile) {
            case 'inactive_farm':
                return [
                    'reserveLF' => 5,
                    'reserveHF' => 2,
                    'reserveCR' => 1,
                    'reserveBS' => 1,
                    'reserveDS' => 0,
                    'reserveBC' => 0,

                    'useLF' => 0.85,
                    'useHF' => 0.70,
                    'useCR' => 0.70,
                    'useBS' => 0.70,
                    'useDS' => 0.70,
                    'useBC' => 0.70,

                    'minSendLF' => 10,
                    'minSendHF' => 2,
                    'minSendCR' => 1,
                    'minSendBS' => 1,
                    'minSendDS' => 0,
                    'minSendBC' => 0,

                    'maxLC' => static::ATTACK_MAX_LARGE_CARGO,
                    'maxSC' => static::ATTACK_MAX_SMALL_CARGO,
                ];

            case 'inactive_defended':
                return [
                    'reserveLF' => 8,
                    'reserveHF' => 3,
                    'reserveCR' => 1,
                    'reserveBS' => 1,
                    'reserveDS' => 0,
                    'reserveBC' => 0,

                    'useLF' => 0.70,
                    'useHF' => 0.65,
                    'useCR' => 0.65,
                    'useBS' => 0.65,
                    'useDS' => 0.60,
                    'useBC' => 0.65,

                    'minSendLF' => 12,
                    'minSendHF' => 2,
                    'minSendCR' => 1,
                    'minSendBS' => 1,
                    'minSendDS' => 0,
                    'minSendBC' => 0,

                    'maxLC' => static::ATTACK_MAX_LARGE_CARGO,
                    'maxSC' => static::ATTACK_MAX_SMALL_CARGO,
                ];

            case 'active_weak':
                return [
                    'reserveLF' => 12,
                    'reserveHF' => 4,
                    'reserveCR' => 2,
                    'reserveBS' => 1,
                    'reserveDS' => 1,
                    'reserveBC' => 1,

                    'useLF' => 0.55,
                    'useHF' => 0.55,
                    'useCR' => 0.60,
                    'useBS' => 0.60,
                    'useDS' => 0.55,
                    'useBC' => 0.60,

                    'minSendLF' => 15,
                    'minSendHF' => 3,
                    'minSendCR' => 1,
                    'minSendBS' => 1,
                    'minSendDS' => 0,
                    'minSendBC' => 0,

                    'maxLC' => static::ATTACK_MAX_LARGE_CARGO,
                    'maxSC' => static::ATTACK_MAX_SMALL_CARGO,
                ];

            default:
                return [
                    'reserveLF' => 999999,
                    'reserveHF' => 999999,
                    'reserveCR' => 999999,
                    'reserveBS' => 999999,
                    'reserveDS' => 999999,
                    'reserveBC' => 999999,

                    'useLF' => 0.0,
                    'useHF' => 0.0,
                    'useCR' => 0.0,
                    'useBS' => 0.0,
                    'useDS' => 0.0,
                    'useBC' => 0.0,

                    'minSendLF' => 0,
                    'minSendHF' => 0,
                    'minSendCR' => 0,
                    'minSendBS' => 0,
                    'minSendDS' => 0,
                    'minSendBC' => 0,

                    'maxLC' => 0,
                    'maxSC' => 0,
                ];
        }
    }

    protected static function getAttackTarget(array $USER, array $PLANET): ?array
    {
        $spyTable = static::getSpyTableName();

        $row = Database::get()->selectSingle(
            "SELECT
                p.*,
                u.username,
                u.onlinetime,
                COALESCE(up.total_points, 0) AS total_points,
                (
                    COALESCE(p.misil_launcher, 0) +
                    COALESCE(p.small_laser, 0) +
                    COALESCE(p.big_laser, 0) +
                    COALESCE(p.gauss_canyon, 0) +
                    COALESCE(p.ionic_canyon, 0) +
                    COALESCE(p.buster_canyon, 0) +
                    COALESCE(p.small_protection_shield, 0) +
                    COALESCE(p.big_protection_shield, 0)
                ) AS defense_count,
                (
                    COALESCE(p.metal, 0) +
                    COALESCE(p.crystal, 0) +
                    COALESCE(p.deuterium, 0)
                ) AS total_resources,
                s.last_spy_time,
                s.spy_count,
                s.is_inactive AS spy_is_inactive,
                s.last_attack_time
             FROM {$spyTable} s
             INNER JOIN %%PLANETS%% p
                ON p.id = s.target_planet_id
             INNER JOIN %%USERS%% u
                ON u.id = p.id_owner
             LEFT JOIN %%USER_POINTS%% up
                ON up.id_owner = u.id
             WHERE s.bot_id = :botId
               AND s.last_spy_time >= :recentSpy
               AND (s.last_attack_time IS NULL OR s.last_attack_time < :attackUnlockTime)
               AND p.planet_type = 1
               AND p.id_owner != :me
               AND u.is_bot = 0
               AND (u.urlaubs_modus = 0 OR u.urlaubs_modus IS NULL)
               AND p.galaxy = :galaxy
               AND p.system BETWEEN :s1 AND :s2
               AND (
                    (
                        u.onlinetime < :inactiveTime
                        AND
                        (
                            COALESCE(p.misil_launcher, 0) +
                            COALESCE(p.small_laser, 0) +
                            COALESCE(p.big_laser, 0) +
                            COALESCE(p.gauss_canyon, 0) +
                            COALESCE(p.ionic_canyon, 0) +
                            COALESCE(p.buster_canyon, 0) +
                            COALESCE(p.small_protection_shield, 0) +
                            COALESCE(p.big_protection_shield, 0)
                        ) <= :maxDefenseInactive
                        AND
                        (
                            COALESCE(p.metal, 0) +
                            COALESCE(p.crystal, 0) +
                            COALESCE(p.deuterium, 0)
                        ) >= :minResourcesInactive
                    )
                    OR
                    (
                        u.onlinetime >= :inactiveTime2
                        AND
                        COALESCE(up.total_points, 0) >= :minPointsActive
                        AND
                        (
                            COALESCE(p.misil_launcher, 0) +
                            COALESCE(p.small_laser, 0) +
                            COALESCE(p.big_laser, 0) +
                            COALESCE(p.gauss_canyon, 0) +
                            COALESCE(p.ionic_canyon, 0) +
                            COALESCE(p.buster_canyon, 0) +
                            COALESCE(p.small_protection_shield, 0) +
                            COALESCE(p.big_protection_shield, 0)
                        ) <= :maxDefenseActive
                        AND
                        (
                            COALESCE(p.metal, 0) +
                            COALESCE(p.crystal, 0) +
                            COALESCE(p.deuterium, 0)
                        ) >= :minResourcesActive
                    )
               )
             ORDER BY
                CASE WHEN u.onlinetime < :inactiveTime3 THEN 0 ELSE 1 END ASC,
                total_resources DESC,
                defense_count ASC,
                s.last_spy_time DESC
             LIMIT 1;",
            [
                ':botId'                => $USER['id'],
                ':me'                   => $USER['id'],
                ':recentSpy'            => TIMESTAMP - static::MAX_SPY_AGE,
                ':attackUnlockTime'     => TIMESTAMP - static::ATTACK_TARGET_LOCK_SECONDS,
                ':galaxy'               => $PLANET['galaxy'],
                ':s1'                   => max(1, (int)$PLANET['system'] - static::MAX_ATTACK_RANGE),
                ':s2'                   => min(499, (int)$PLANET['system'] + static::MAX_ATTACK_RANGE),
                ':inactiveTime'         => TIMESTAMP - static::INACTIVE_AFTER,
                ':inactiveTime2'        => TIMESTAMP - static::INACTIVE_AFTER,
                ':inactiveTime3'        => TIMESTAMP - static::INACTIVE_AFTER,
                ':minPointsActive'      => self::MIN_TARGET_POINTS,
                ':maxDefenseInactive'   => 20,
                ':maxDefenseActive'     => 10,
                ':minResourcesInactive' => static::MIN_RESOURCES_INACTIVE,
                ':minResourcesActive'   => static::MIN_RESOURCES_ACTIVE,
            ]
        );

        static::log('ATTACK query result', is_array($row) ? [
            'targetPlanetId'  => $row['id'] ?? null,
            'targetOwner'     => $row['id_owner'] ?? null,
            'defense_count'   => $row['defense_count'] ?? null,
            'onlinetime'      => $row['onlinetime'] ?? null,
            'username'        => $row['username'] ?? null,
            'total_points'    => $row['total_points'] ?? null,
            'total_resources' => $row['total_resources'] ?? null,
            'last_spy_time'   => $row['last_spy_time'] ?? null,
            'last_attack_time'=> $row['last_attack_time'] ?? null,
            'spy_count'       => $row['spy_count'] ?? null,
            'profile'         => is_array($row) ? static::getTargetProfile($row) : null,
        ] : []);

        return is_array($row) ? $row : null;
    }

    protected static function sanitizeFleetByCurrentPlanet(array $PLANET, array $fleetArray): array
    {
        $available = [
            static::SHIP_SMALL_CARGO   => (int)($PLANET['small_ship_cargo'] ?? 0),
            static::SHIP_LARGE_CARGO   => (int)($PLANET['big_ship_cargo'] ?? 0),
            static::SHIP_LIGHT_FIGHTER => (int)($PLANET['light_hunter'] ?? 0),
            static::SHIP_HEAVY_FIGHTER => (int)($PLANET['heavy_hunter'] ?? 0),
            static::SHIP_CRUISER       => (int)($PLANET['crusher'] ?? 0),
            static::SHIP_BATTLESHIP    => (int)($PLANET['battle_ship'] ?? 0),
            static::SHIP_SPY           => (int)($PLANET['spy_sonde'] ?? 0),
            static::SHIP_DESTROYER     => (int)($PLANET['destructor'] ?? 0),
            static::SHIP_BATTLECRUISER => (int)($PLANET['battleship'] ?? 0),
        ];

        foreach ($fleetArray as $shipId => $amount) {
            $amount = (int)$amount;
            $have   = (int)($available[$shipId] ?? 0);

            if ($amount > $have) {
                static::log('ATTACK fleet sanitize reduce', [
                    'planetId'  => $PLANET['id'] ?? null,
                    'shipId'    => $shipId,
                    'wanted'    => $amount,
                    'available' => $have,
                ]);

                if ($have > 0) {
                    $fleetArray[$shipId] = $have;
                } else {
                    unset($fleetArray[$shipId]);
                }
            }
        }

        return $fleetArray;
    }

    protected static function countCombatShipsInFleet(array $fleetArray): int
    {
        return
            (int)($fleetArray[static::SHIP_LIGHT_FIGHTER] ?? 0) +
            (int)($fleetArray[static::SHIP_HEAVY_FIGHTER] ?? 0) +
            (int)($fleetArray[static::SHIP_CRUISER] ?? 0) +
            (int)($fleetArray[static::SHIP_BATTLESHIP] ?? 0) +
            (int)($fleetArray[static::SHIP_DESTROYER] ?? 0) +
            (int)($fleetArray[static::SHIP_BATTLECRUISER] ?? 0);
    }

    protected static function sendAttackFleet(array $USER, array $PLANET, array $TARGET, array $fleetArray): void
    {
        $USER = static::normalizeUserFactors($USER);

        require_once ROOT_PATH . 'includes/classes/class.FleetFunctions.php';

        $freshPlanet = static::reloadPlanet((int)$PLANET['id']);
        if (!$freshPlanet) {
            static::log('ATTACK sendFleet planet reload failed', [
                'planetId' => $PLANET['id'] ?? null,
            ]);
            return;
        }

        $PLANET = $freshPlanet;
        $fleetArray = static::sanitizeFleetByCurrentPlanet($PLANET, $fleetArray);

        static::log('ATTACK fleet after sanitize', [
            'planetId' => $PLANET['id'],
            'fleet'    => $fleetArray,
        ]);

        $combatShips = static::countCombatShipsInFleet($fleetArray);
        if ($combatShips < static::MIN_COMBAT_SHIPS) {
            static::log('ATTACK abort after sanitize, too few combat ships', [
                'planetId'    => $PLANET['id'],
                'combatShips' => $combatShips,
                'min'         => static::MIN_COMBAT_SHIPS,
            ]);
            return;
        }

        if (empty($fleetArray)) {
            static::log('ATTACK abort after sanitize, empty fleet', [
                'planetId' => $PLANET['id'],
            ]);
            return;
        }

        $distance = FleetFunctions::GetTargetDistance(
            [$PLANET['galaxy'], $PLANET['system'], $PLANET['planet']],
            [$TARGET['galaxy'], $TARGET['system'], $TARGET['planet']]
        );

        $speed = FleetFunctions::GetFleetMaxSpeed($fleetArray, $USER);

        $duration = (int)FleetFunctions::GetMissionDuration(
            10,
            $speed,
            $distance,
            FleetFunctions::GetGameSpeedFactor(),
            $USER
        );

        static::log('ATTACK sendFleet', [
            'fromPlanetId' => $PLANET['id'],
            'fleetArray'   => $fleetArray,
            'distance'     => $distance,
            'speed'        => $speed,
            'duration'     => $duration,
            'targetId'     => $TARGET['id'] ?? null,
            'profile'      => static::getTargetProfile($TARGET),
        ]);

        $fleetStartTime = TIMESTAMP;
        $fleetStayTime  = TIMESTAMP + $duration;
        $fleetEndTime   = TIMESTAMP + ($duration * 2);

        FleetFunctions::sendFleet(
            $fleetArray,
            1,
            $USER['id'],
            $PLANET['id'],
            $PLANET['galaxy'],
            $PLANET['system'],
            $PLANET['planet'],
            1,
            $TARGET['id_owner'],
            $TARGET['id'],
            $TARGET['galaxy'],
            $TARGET['system'],
            $TARGET['planet'],
            1,
            [901 => 0, 902 => 0, 903 => 0],
            $fleetStartTime,
            $fleetStayTime,
            $fleetEndTime
        );
    }
}