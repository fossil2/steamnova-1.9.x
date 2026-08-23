<?php
declare(strict_types=1);

require_once ROOT_PATH . 'includes/classes/aibot/BotAICommon.php';

class BotAISpy extends BotAICommon
{
    private const SPY_PROBES_STANDARD = 4;
    private const SPY_PROBES_FULL     = 8;

    private const SPY_MAX_NORMAL_PER_DAY   = 1;
    private const SPY_MAX_INACTIVE_PER_DAY = 2;

    private const SPY_TARGET_RANGE = 100;

    public static function runPlanet(array $USER, array $PLANET): bool
    {
        if (isset($USER['bot_enable_spy']) && (int)$USER['bot_enable_spy'] !== 1) {
            static::log('SPY disabled by toggle', [
                'userId'   => $USER['id'] ?? null,
                'planetId' => $PLANET['id'] ?? null,
            ]);
            return false;
        }

        static::log('SPY start', [
            'userId'   => $USER['id'],
            'planetId' => $PLANET['id'],
        ]);

        if (!empty($USER['bot_next_spy']) && $USER['bot_next_spy'] > TIMESTAMP) {
            static::log('SPY cooldown active', [
                'next' => $USER['bot_next_spy'],
                'now'  => TIMESTAMP,
            ]);
            return false;
        }

        if (
            (int)($PLANET['hangar'] ?? 0) < static::MIN_HANGAR ||
            (int)($USER['combustion_tech'] ?? 0) < static::MIN_COMBUSTION ||
            (int)($USER['spy_tech'] ?? 0) < static::MIN_SPY_TECH
        ) {
            static::log('SPY requirements not met', [
                'hangar'          => $PLANET['hangar'] ?? 0,
                'combustion_tech' => $USER['combustion_tech'] ?? 0,
                'spy_tech'        => $USER['spy_tech'] ?? 0,
            ]);
            return false;
        }

        $haveProbes    = (int)($PLANET['spy_sonde'] ?? 0);
        $queuedProbes  = static::getQueuedAmount((int)$PLANET['id'], static::SHIP_SPY);
        $plannedProbes = $haveProbes + $queuedProbes;

        if ($plannedProbes < static::SPY_PROBES_STANDARD) {
            $missing = static::SPY_PROBES_STANDARD - $plannedProbes;

            static::log('SPY queue sondes', [
                'planetId' => $PLANET['id'],
                'have'     => $haveProbes,
                'queued'   => $queuedProbes,
                'planned'  => $plannedProbes,
                'need'     => static::SPY_PROBES_STANDARD,
                'missing'  => $missing,
            ]);

            static::queueShip((int)$PLANET['id'], static::SHIP_SPY, $missing);
            return false;
        }

        if ($haveProbes < static::SPY_PROBES_STANDARD) {
            static::log('SPY sondes still in production', [
                'planetId' => $PLANET['id'],
                'have'     => $haveProbes,
                'queued'   => $queuedProbes,
                'need'     => static::SPY_PROBES_STANDARD,
            ]);
            return false;
        }

        $TARGET = static::getSpyTarget($USER, $PLANET);

        static::log('SPY target', is_array($TARGET) ? [
            'targetPlanetId' => $TARGET['id'] ?? null,
            'targetOwner'    => $TARGET['id_owner'] ?? null,
            'galaxy'         => $TARGET['galaxy'] ?? null,
            'system'         => $TARGET['system'] ?? null,
            'planet'         => $TARGET['planet'] ?? null,
            'inactive'       => $TARGET['is_inactive'] ?? null,
        ] : []);

        if (!$TARGET) {
            static::log('SPY no target found', [
                'userId'   => $USER['id'] ?? null,
                'planetId' => $PLANET['id'] ?? null,
                'galaxy'   => $PLANET['galaxy'] ?? null,
                'system'   => $PLANET['system'] ?? null,
            ]);
            return false;
        }

        if (!static::canSpyTargetToday((int)$USER['id'], $TARGET)) {
            static::log('SPY target daily limit reached', [
                'botId'          => $USER['id'],
                'targetPlanetId' => $TARGET['id'] ?? null,
                'targetOwnerId'  => $TARGET['id_owner'] ?? null,
                'inactive'       => $TARGET['is_inactive'] ?? null,
            ]);

            Database::get()->update(
                'UPDATE %%USERS%% SET bot_next_spy = :t WHERE id = :id;',
                [
                    ':t'  => TIMESTAMP + mt_rand(1800, 7200),
                    ':id' => $USER['id'],
                ]
            );

            return false;
        }

        $probeCount = static::determineProbeCount($PLANET, $TARGET, $plannedProbes);

        if ($plannedProbes < $probeCount) {
            $need = $probeCount - $plannedProbes;

            if ($need > 0) {
                static::queueShip((int)$PLANET['id'], static::SHIP_SPY, $need);

                static::log('SPY queue more sondes for target', [
                    'planetId'   => $PLANET['id'],
                    'targetId'   => $TARGET['id'] ?? null,
                    'have'       => $haveProbes,
                    'queued'     => $queuedProbes,
                    'planned'    => $plannedProbes,
                    'needTotal'  => $probeCount,
                    'queueNeed'  => $need,
                ]);
            }

            return false;
        }

        if ($haveProbes < $probeCount) {
            static::log('SPY waiting for enough ready sondes', [
                'planetId'   => $PLANET['id'],
                'targetId'   => $TARGET['id'] ?? null,
                'have'       => $haveProbes,
                'planned'    => $plannedProbes,
                'needTotal'  => $probeCount,
            ]);
            return false;
        }

        static::sendSpyFleet($USER, $PLANET, $TARGET, $probeCount);
        static::registerSpyTarget((int)$USER['id'], $TARGET);

        Database::get()->update(
            'UPDATE %%USERS%% SET bot_next_spy = :t WHERE id = :id;',
            [
                ':t'  => TIMESTAMP + mt_rand(6 * 3600, 18 * 3600),
                ':id' => $USER['id'],
            ]
        );

        static::log('SPY cooldown set', ['userId' => $USER['id']]);
        return true;
    }

    protected static function getSpyTarget(array $USER, array $PLANET): ?array
    {
        $row = Database::get()->selectSingle(
            'SELECT
                p.*,
                u.onlinetime,
                CASE
                    WHEN u.onlinetime < :inactiveTime THEN 1
                    ELSE 0
                END AS is_inactive
             FROM %%PLANETS%% p
             JOIN %%USERS%% u ON u.id = p.id_owner
             WHERE u.is_bot = 0
               AND p.id_owner != :me
               AND p.planet_type = 1
               AND p.galaxy = :gal
               AND p.system BETWEEN :s1 AND :s2
             ORDER BY
                is_inactive DESC,
                RAND()
             LIMIT 1;',
            [
                ':me'           => $USER['id'],
                ':gal'          => $PLANET['galaxy'],
                ':s1'           => max(1, (int)$PLANET['system'] - static::SPY_TARGET_RANGE),
                ':s2'           => min(499, (int)$PLANET['system'] + static::SPY_TARGET_RANGE),
                ':inactiveTime' => TIMESTAMP - static::INACTIVE_AFTER,
            ]
        );

        return is_array($row) ? $row : null;
    }

    protected static function canSpyTargetToday(int $botId, array $TARGET): bool
    {
        $dayStart   = strtotime(date('Y-m-d 00:00:00', TIMESTAMP));
        $isInactive = (int)($TARGET['is_inactive'] ?? 0) === 1;
        $table      = static::getSpyTableName();

        $row = Database::get()->selectSingle(
            "SELECT spy_count, last_spy_time
             FROM {$table}
             WHERE bot_id = :botId
               AND target_planet_id = :targetPlanetId
             LIMIT 1;",
            [
                ':botId'          => $botId,
                ':targetPlanetId' => (int)$TARGET['id'],
            ]
        );

        if (!$row) {
            return true;
        }

        $lastSpyTime = (int)($row['last_spy_time'] ?? 0);

        if ($lastSpyTime < $dayStart) {
            return true;
        }

        $count = (int)($row['spy_count'] ?? 0);
        $limit = $isInactive ? static::SPY_MAX_INACTIVE_PER_DAY : static::SPY_MAX_NORMAL_PER_DAY;

        return $count < $limit;
    }

    protected static function registerSpyTarget(int $botId, array $TARGET): void
    {
        $dayStart   = strtotime(date('Y-m-d 00:00:00', TIMESTAMP));
        $isInactive = (int)($TARGET['is_inactive'] ?? 0);
        $table      = static::getSpyTableName();

        $row = Database::get()->selectSingle(
            "SELECT id, spy_count, last_spy_time
             FROM {$table}
             WHERE bot_id = :botId
               AND target_planet_id = :targetPlanetId
             LIMIT 1;",
            [
                ':botId'          => $botId,
                ':targetPlanetId' => (int)$TARGET['id'],
            ]
        );

        if ($row) {
            $lastSpyTime = (int)($row['last_spy_time'] ?? 0);
            $newCount    = ($lastSpyTime >= $dayStart)
                ? ((int)$row['spy_count'] + 1)
                : 1;

            Database::get()->update(
                "UPDATE {$table}
                 SET spy_count = :count,
                     last_spy_time = :time,
                     is_inactive = :inactive,
                     target_owner_id = :targetOwnerId
                 WHERE id = :id;",
                [
                    ':count'         => $newCount,
                    ':time'          => TIMESTAMP,
                    ':inactive'      => $isInactive,
                    ':targetOwnerId' => (int)$TARGET['id_owner'],
                    ':id'            => (int)$row['id'],
                ]
            );
        } else {
            Database::get()->insert(
                "INSERT INTO {$table}
                    (bot_id, target_owner_id, target_planet_id, is_inactive, spy_count, last_spy_time)
                 VALUES
                    (:botId, :targetOwnerId, :targetPlanetId, :inactive, 1, :time);",
                [
                    ':botId'          => $botId,
                    ':targetOwnerId'  => (int)$TARGET['id_owner'],
                    ':targetPlanetId' => (int)$TARGET['id'],
                    ':inactive'       => $isInactive,
                    ':time'           => TIMESTAMP,
                ]
            );
        }

        static::log('SPY target registered', [
            'botId'          => $botId,
            'targetPlanetId' => $TARGET['id'] ?? null,
            'targetOwnerId'  => $TARGET['id_owner'] ?? null,
            'inactive'       => $isInactive,
        ]);
    }

    protected static function determineProbeCount(array $PLANET, array $TARGET, int $plannedProbes): int
    {
        $isInactive = (int)($TARGET['is_inactive'] ?? 0) === 1;

        if ($isInactive) {
            return min($plannedProbes, static::SPY_PROBES_FULL);
        }

        return min($plannedProbes, static::SPY_PROBES_STANDARD);
    }

    protected static function sendSpyFleet(array $USER, array $PLANET, array $TARGET, int $probeCount): void
    {
        $USER = static::normalizeUserFactors($USER);

        require_once ROOT_PATH . 'includes/classes/class.FleetFunctions.php';

        $fleetArray = [static::SHIP_SPY => $probeCount];

        $distance = FleetFunctions::GetTargetDistance(
            [$PLANET['galaxy'], $PLANET['system'], $PLANET['planet']],
            [$TARGET['galaxy'], $TARGET['system'], $TARGET['planet']]
        );

        $speed = FleetFunctions::GetFleetMaxSpeed($fleetArray, $USER);

        $duration = FleetFunctions::GetMissionDuration(
            10,
            $speed,
            $distance,
            FleetFunctions::GetGameSpeedFactor(),
            $USER
        );

        static::log('SPY sendFleet', [
            'fromPlanetId' => $PLANET['id'],
            'distance'     => $distance,
            'speed'        => $speed,
            'duration'     => $duration,
            'targetId'     => $TARGET['id'] ?? null,
            'probeCount'   => $probeCount,
        ]);

        $spyStartTime = TIMESTAMP;
        $spyStayTime  = TIMESTAMP + (int)$duration;
        $spyEndTime   = TIMESTAMP + ((int)$duration * 2);

        FleetFunctions::sendFleet(
            $fleetArray,
            6,
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
            $spyStartTime,
            $spyStayTime,
            $spyEndTime
        );
    }

    protected static function getSpyTableName(): string
    {
        $prefix = DB_PREFIX;
        if (substr($prefix, -1) !== '_') {
            $prefix .= '_';
        }

        return $prefix . 'spy_bot_targets';
    }
}