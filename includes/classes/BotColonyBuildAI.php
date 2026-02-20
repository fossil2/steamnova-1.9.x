<?php
declare(strict_types=1);

class BotColonyBuildAI
{
    /* =========================
     * ENTRY
     * ========================= */
    public static function run(int $userId): void
    {
        $db = Database::get();

        /* USER */
        $USER = $db->selectSingle(
    "SELECT *
     FROM " . DB_PREFIX . "users
     WHERE id = :uid
       AND is_bot = 1",
            [':uid' => $userId]
        );

        if (!$USER) {
            return;
        }

        /* ALLE KOLONIEN LADEN (ohne Hauptplanet) */
        $colonies = $db->select(
            "SELECT *
             FROM " . DB_PREFIX . "planets
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY id ASC",
            [':uid' => $userId]
        );

        if (empty($colonies)) {
            return;
        }

        /* Jede Kolonie prüfen – aber max. 1 Bau insgesamt */
        foreach ($colonies as $PLANET) {

              $res = new ResourceUpdate();
           $res->CalcResource($USER, $PLANET, false);

            // Hauptplanet überspringen (der erste)
            if ((int)$PLANET['id'] === self::getMainPlanetId($userId)) {
                continue;
            }

            // Bauqueue aktiv?
            if ((int)$PLANET['b_building'] > time()) {
                continue;
            }

           // ⭐ Defense Test zuerst
          if (self::tryBuildDefense($PLANET)) {
           return;
            }

     // Danach normales Gebäude-Bauen
            if (self::tryBuild($PLANET)) {
              return;
              }

        }
    }

    /* =========================
     * TRY BUILD
     * ========================= */
    private static function tryBuild(array $p): bool
    {
        $nextMineLevel = max(
            (int)$p['metal_mine'],
            (int)$p['crystal_mine'],
            (int)$p['deuterium_sintetizer']
        ) + 1;

        $buildOrder = [
            self::ID_SOLAR_PLANT,
            self::ID_ROBOT_FACTORY,

            self::ID_METAL_MINE,
            self::ID_CRYSTAL_MINE,
            self::ID_DEUTERIUM_SYNTH,

            self::ID_METAL_STORE,
            self::ID_CRYSTAL_STORE,
            self::ID_DEUTERIUM_STORE,

            self::ID_HANGAR,
        ];

        foreach ($buildOrder as $elementId) {
            if (!self::shouldBuild($p, $elementId, $nextMineLevel)) {
                continue;
            }

            if (self::startBuild($p, $elementId)) {
                return true;
            }
        }

        return false;
    }

    /* =========================
 * TEST DEFENSE BUILD
 * ========================= */
private static function tryBuildDefense(array $p): bool
{
    global $resource;

    // Hangar Voraussetzung
    if ((int)$p['hangar'] < 2) {
        return false;
    }

    $defIds = [401, 402, 403];

    foreach ($defIds as $id) {

        if (!isset($resource[$id])) {
            continue;
        }

        $field = $resource[$id];
        $count = (int)($p[$field] ?? 0);

        // ⭐ kleine Test-Defense
        $limit = match ($id) {
            401 => 6,
            402 => 4,
            403 => 2,
            default => 0,
        };

        if ($count >= $limit) {
            continue;
        }

        if (self::startDefenseBuild($p, $id)) {
            return true;
        }
    }

    return false;
}

private static function startDefenseBuild(array $planet, int $elementId): bool
{
    $cost = BuildFunctions::getElementPrice(
        [],
        $planet,
        $elementId,
        false,
        1
    );

    if (
        $planet['metal']     < ($cost[901] ?? 0) ||
        $planet['crystal']   < ($cost[902] ?? 0) ||
        $planet['deuterium'] < ($cost[903] ?? 0)
    ) {
        return false;
    }

    $now = time();
    $end = $now + 12;

    $queue = serialize([
        [$elementId, 1, [], (float)$end, 'build']
    ]);

    Database::get()->update(
        "UPDATE " . DB_PREFIX . "planets SET
            metal         = metal - :m,
            crystal       = crystal - :c,
            deuterium     = deuterium - :d,
            b_building    = :end,
            b_building_id = :queue
         WHERE id = :pid",
        [
            ':m'     => (int)($cost[901] ?? 0),
            ':c'     => (int)($cost[902] ?? 0),
            ':d'     => (int)($cost[903] ?? 0),
            ':end'   => $end,
            ':queue' => $queue,
            ':pid'   => $planet['id'],
        ]
    );

    return true;
}

    /* =========================
     * SHOULD BUILD ?
     * ========================= */
    private static function shouldBuild(array $p, int $id, int $nextMineLevel): bool
    {
        // 🔋 Energie-Zwang
        if ((int)$p['energy'] < 0 && $id !== self::ID_SOLAR_PLANT) {
            return false;
        }

        return match ($id) {

            self::ID_SOLAR_PLANT =>
                (int)$p['solar_plant'] === 0 ||
                self::needsMoreEnergy($p, $nextMineLevel),

            self::ID_METAL_MINE      => $p['metal_mine'] < 20,
            self::ID_CRYSTAL_MINE    => $p['crystal_mine'] < 15,
            self::ID_DEUTERIUM_SYNTH => $p['deuterium_sintetizer'] < 10,

            self::ID_METAL_STORE     => $p['metal_store'] < 5,
            self::ID_CRYSTAL_STORE   => $p['crystal_store'] < 4,
            self::ID_DEUTERIUM_STORE => $p['deuterium_store'] < 3,

            self::ID_ROBOT_FACTORY   => $p['robot_factory'] < 10,
            self::ID_HANGAR          => $p['hangar'] < 7,

            default => false,
        };
    }

    /* =========================
     * ⚡ ENERGY CHECK
     * ========================= */
    private static function needsMoreEnergy(array $p, int $nextMineLevel): bool
    {
        $solarOutput = max(0, (int)$p['solar_plant']) * 55;

        $currentNeed =
            ((int)$p['metal_mine']           * 10) +
            ((int)$p['crystal_mine']         * 15) +
            ((int)$p['deuterium_sintetizer'] * 25);

        $futureNeed = $currentNeed + ($nextMineLevel * 18);

        return $solarOutput < ($futureNeed * 1.30);
    }

    /* =========================
     * START BUILD
     * ========================= */
    private static function startBuild(array $planet, int $elementId): bool
    {
        global $resource;

        if (!isset($resource[$elementId])) {
            return false;
        }

        $field       = $resource[$elementId];
        $levelBefore = (int)$planet[$field];
        $targetLevel = $levelBefore + 1;

        $cost = self::getBuildCost($elementId, $targetLevel);

        if (
            $planet['metal']     < $cost['metal'] ||
            $planet['crystal']   < $cost['crystal'] ||
            $planet['deuterium'] < $cost['deuterium']
        ) {
            return false;
        }

        $now = time();
        $end = $now + 10; // Testdauer (wie bei dir)

        $queue = serialize([
            [$elementId, $targetLevel, [], (float)$end, 'build']
        ]);

        Database::get()->update(
            "UPDATE " . DB_PREFIX . "planets SET
                metal         = metal - :m,
                crystal       = crystal - :c,
                deuterium     = deuterium - :d,
                b_building    = :end,
                b_building_id = :queue
             WHERE id = :pid",
            [
                ':m'     => $cost['metal'],
                ':c'     => $cost['crystal'],
                ':d'     => $cost['deuterium'],
                ':end'   => $end,
                ':queue' => $queue,
                ':pid'   => $planet['id'],
            ]
        );

        return true;
    }

    /* =========================
     * BUILD COST
     * ========================= */
    private static function getBuildCost(int $id, int $level): array
    {
        $v = Database::get()->selectSingle(
            "SELECT factor, cost901, cost902, cost903
             FROM " . DB_PREFIX . "vars
             WHERE elementID = :id",
            [':id' => $id]
        );

        if (!$v) {
            return ['metal'=>0,'crystal'=>0,'deuterium'=>0];
        }

        $factor = (float)$v['factor'];

        return [
            'metal'     => (int)floor($v['cost901'] * pow($factor, $level - 1)),
            'crystal'   => (int)floor($v['cost902'] * pow($factor, $level - 1)),
            'deuterium' => (int)floor($v['cost903'] * pow($factor, $level - 1)),
        ];
    }

    /* =========================
     * MAIN PLANET ID
     * ========================= */
    private static function getMainPlanetId(int $uid): int
    {
        $row = Database::get()->selectSingle(
            "SELECT id FROM " . DB_PREFIX . "planets
             WHERE id_owner = :uid AND planet_type = 1
             ORDER BY id ASC LIMIT 1",
            [':uid' => $uid]
        );

        return (int)($row['id'] ?? 0);
    }

    /* =========================
     * BUILDING IDS
     * ========================= */
    private const ID_METAL_MINE      = 1;
    private const ID_CRYSTAL_MINE    = 2;
    private const ID_DEUTERIUM_SYNTH = 3;
    private const ID_SOLAR_PLANT     = 4;
    private const ID_ROBOT_FACTORY   = 14;
    private const ID_HANGAR          = 21;
    private const ID_METAL_STORE     = 22;
    private const ID_CRYSTAL_STORE   = 23;
    private const ID_DEUTERIUM_STORE = 24;
}
