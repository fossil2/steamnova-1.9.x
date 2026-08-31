<?php
declare(strict_types=1);

require_once ROOT_PATH . 'includes/classes/aibot/BotAIResourceTransfer.php';

class BotBuildAI
{
    /**
     * Ab dieser Punktzahl schaltet der Hauptplanet
     * in den Eco-Fokus: Minen + Solar weiter pushen
     */
    private const ECO_FOCUS_POINTS = 35000;

    /* =========================
     * ENTRY POINT
     * ========================= */
    public static function run(int $userId): void
    {
        self::log([
            'action' => 'RUN_CALLED',
            'userId' => $userId,
        ]);

        $db = Database::get();

        /* USER */
        $USER = $db->selectSingle(
            "SELECT *
             FROM " . DB_PREFIX . "users
             WHERE id = :uid",
            [':uid' => $userId]
        );

        if (!$USER) {
            self::log(['action' => 'NO_USER']);
            return;
        }

        /* PLANET */
        $PLANET = self::getMainPlanet($userId);

        if (!$PLANET) {
            self::log(['action' => 'NO_PLANET']);
            return;
        }

        /* Queue aktiv? → abbrechen */
        if ((int)$PLANET['b_building'] > time()) {
            self::log([
                'action' => 'QUEUE_ACTIVE',
                'planet_id' => $PLANET['id'],
                'until'  => $PLANET['b_building']
            ]);
            return;
        }

        $points = self::getUserPoints($userId, $USER);
        $ecoFocus = ($points >= self::ECO_FOCUS_POINTS);

        self::log([
            'action'    => 'STATE',
            'planet_id' => $PLANET['id'],
            'points'    => $points,
            'ecoFocus'  => $ecoFocus ? 1 : 0,
            'freeFields'=> self::getFreeFields($PLANET),
        ]);

        /* =========================
         * BUILD ORDER
         * ========================= */
        $buildOrder = [
            /* Wenn keine freien Felder vorhanden sind */
            self::ID_TERRAFORMER,

            /* Energie immer zuerst prüfbar */
            self::ID_SOLAR_PLANT,

            /* Eco Core */
            self::ID_METAL_MINE,
            self::ID_CRYSTAL_MINE,
            self::ID_DEUTERIUM_SYNTH,

            /* Speicher dynamisch */
            self::ID_METAL_STORE,
            self::ID_CRYSTAL_STORE,
            self::ID_DEUTERIUM_STORE,

            /* Infra */
            self::ID_ROBOT_FACTORY,
            self::ID_NANITE_FACTORY,
            self::ID_HANGAR,
            self::ID_SILO,

            /* Forschung */
            self::ID_LABORATORY,
        ];

        foreach ($buildOrder as $elementId) {
            if (!self::shouldBuild($PLANET, $elementId, $points)) {
                continue;
            }

            if (self::startBuild($PLANET, $elementId)) {
                self::log([
                    'action'    => 'RUN_FINISHED_BUILD_STARTED',
                    'planet_id' => $PLANET['id'],
                    'elementId' => $elementId,
                    'points'    => $points,
                ]);
                return;
            }
        }

        self::log([
            'action'    => 'NO_ACTION',
            'planet_id' => $PLANET['id'],
            'points'    => $points,
        ]);
    }

    /* =========================
     * SHOULD BUILD ?
     * ========================= */
    private static function shouldBuild(array $p, int $id, int $points = 0): bool
    {
        $metal   = (int)($p['metal_mine'] ?? 0);
        $crystal = (int)($p['crystal_mine'] ?? 0);
        $deut    = (int)($p['deuterium_sintetizer'] ?? 0);
        $solar   = (int)($p['solar_plant'] ?? 0);

        $ecoFocus = ($points >= self::ECO_FOCUS_POINTS);

        $midgame =
            $metal >= 22 ||
            $crystal >= 18 ||
            $deut >= 12;

        $nextMineLevel = max($metal, $crystal, $deut) + 1;

        $needsEnergy = self::needsMoreEnergy($p, $nextMineLevel);

        if (
            $needsEnergy &&
            in_array($id, [
                self::ID_METAL_MINE,
                self::ID_CRYSTAL_MINE,
                self::ID_DEUTERIUM_SYNTH
            ], true)
        ) {
            return false;
        }

        if ($ecoFocus) {
            return match ($id) {
                self::ID_TERRAFORMER =>
                    self::getFreeFields($p) <= 0,

                self::ID_SOLAR_PLANT =>
                    $needsEnergy ||
                    $solar < (int)ceil(max($metal, $crystal, $deut) * 0.85),

                self::ID_METAL_MINE =>
                    $metal <= ($crystal + 2),

                self::ID_CRYSTAL_MINE =>
                    $crystal <= $metal,

                self::ID_DEUTERIUM_SYNTH =>
                    $deut <= ($crystal - 3),

                self::ID_METAL_STORE =>
                    self::isStorageNeeded($p, 'metal'),

                self::ID_CRYSTAL_STORE =>
                    self::isStorageNeeded($p, 'crystal'),

                self::ID_DEUTERIUM_STORE =>
                    self::isStorageNeeded($p, 'deuterium'),

                self::ID_ROBOT_FACTORY,
                self::ID_NANITE_FACTORY,
                self::ID_HANGAR,
                self::ID_LABORATORY => false,

                self::ID_SILO =>
                    (int)($p['hangar'] ?? 0) >= 1
                    && (int)($p['silo'] ?? 0) < 4,

                default => false,
            };
        }

        return match ($id) {
            self::ID_TERRAFORMER =>
                self::getFreeFields($p) <= 0,

            self::ID_SOLAR_PLANT =>
                $solar === 0 || $needsEnergy,

            self::ID_METAL_MINE =>
                $midgame
                    ? $metal <= ($crystal + 3)
                    : $metal < 22,

            self::ID_CRYSTAL_MINE =>
                $midgame
                    ? $crystal <= ($metal - 1)
                    : $crystal < 18,

            self::ID_DEUTERIUM_SYNTH =>
                $midgame
                    ? $deut <= ($crystal - 2)
                    : $deut < 12,

            self::ID_METAL_STORE =>
                self::isStorageNeeded($p, 'metal'),

            self::ID_CRYSTAL_STORE =>
                self::isStorageNeeded($p, 'crystal'),

            self::ID_DEUTERIUM_STORE =>
                self::isStorageNeeded($p, 'deuterium'),

            self::ID_ROBOT_FACTORY =>
                (int)($p['robot_factory'] ?? 0) < 10
                || (
                    $midgame
                    && (int)($p['robot_factory'] ?? 0) < floor($metal / 4)
                ),

            self::ID_NANITE_FACTORY =>
                $midgame
                && (int)($p['robot_factory'] ?? 0) >= 10
                && (int)($p['nanite_factory'] ?? 0) < 1,

            self::ID_HANGAR =>
                (int)($p['hangar'] ?? 0) < ($midgame ? 12 : 6),

            self::ID_SILO =>
                (int)($p['hangar'] ?? 0) >= 1
                && (int)($p['silo'] ?? 0) < 4,

            self::ID_LABORATORY =>
                (int)($p['laboratory'] ?? 0) < floor($crystal / ($midgame ? 3 : 5)),

            default => false,
        };
    }

    /* =========================
     * ENERGY CHECK
     * ========================= */
    private static function needsMoreEnergy(array $p, int $nextMineLevel): bool
    {
        $solarPlant = (int)($p['solar_plant'] ?? 0);

        $solarOutput = max(0, $solarPlant) * 55;

        $currentNeed =
              ((int)($p['metal_mine'] ?? 0) * 10)
            + ((int)($p['crystal_mine'] ?? 0) * 15)
            + ((int)($p['deuterium_sintetizer'] ?? 0) * 25);

        $futureNeed = $currentNeed + ($nextMineLevel * 18);

        $requiredWithBuffer = (int)ceil($futureNeed * 1.30);

        return $solarOutput < $requiredWithBuffer;
    }

    private static function isStorageNeeded(array $p, string $res): bool
    {
        $storage = match ($res) {
            'metal'     => (int)($p['metal_store'] ?? 0),
            'crystal'   => (int)($p['crystal_store'] ?? 0),
            'deuterium' => (int)($p['deuterium_store'] ?? 0),
            default     => 0,
        };

        $capacity = pow(1.5, $storage) * 5000;

        return (float)($p[$res] ?? 0) > ($capacity * 0.80);
    }

    /* =========================
     * USER POINTS
     * ========================= */
    private static function getUserPoints(int $userId, array $USER = []): int
    {
        try {
            if (isset($USER['total_points'])) {
                return (int)$USER['total_points'];
            }

            if (isset($USER['points'])) {
                return (int)$USER['points'];
            }
        } catch (\Throwable $e) {
        }

        try {
            $row = Database::get()->selectSingle(
                "SELECT total_points
                 FROM " . DB_PREFIX . "user_points
                 WHERE id_owner = :uid
                 LIMIT 1",
                [':uid' => $userId]
            );

            if ($row && isset($row['total_points'])) {
                return (int)$row['total_points'];
            }
        } catch (\Throwable $e) {
            self::log([
                'action'  => 'POINTS_LOOKUP_FAILED',
                'source'  => 'user_points.total_points',
                'message' => $e->getMessage(),
            ]);
        }

        return 0;
    }

    /* =========================
     * PLANET FIELDS
     * ========================= */
    private static function getFreeFields(array $planet): int
    {
        $used = (int)($planet['field_current'] ?? 0);
        $max  = (int)($planet['field_max'] ?? 0);

        if ($max <= 0) {
            return 999;
        }

        return max(0, $max - $used);
    }

    private static function needsTerraformer(array $planet): bool
    {
        return self::getFreeFields($planet) <= 0;
    }

    /* =========================
     * START BUILD
     * ========================= */
    private static function startBuild(array $planet, int $elementId): bool
    {
        global $resource;

        if (
            $elementId !== self::ID_TERRAFORMER
            && self::needsTerraformer($planet)
        ) {
            self::log([
                'action'     => 'NO_FREE_FIELDS_TRY_TERRAFORMER',
                'planet_id'  => $planet['id'] ?? 0,
                'wantedId'   => $elementId,
                'freeFields' => self::getFreeFields($planet),
            ]);

            return self::startBuild(
                $planet,
                self::ID_TERRAFORMER
            );
        }

        if (!isset($resource[$elementId])) {
            self::log([
                'action' => 'UNKNOWN_ELEMENT',
                'id'     => $elementId
            ]);

            return false;
        }

        $field       = $resource[$elementId];
        $levelBefore = (int)($planet[$field] ?? 0);
        $targetLevel = $levelBefore + 1;

        $cost = self::getBuildCost(
            $elementId,
            $targetLevel
        );

        /* =========================
         * RESSOURCENPRÜFUNG
         * ========================= */
        if (
            (float)($planet['metal'] ?? 0) < $cost['metal']
            ||
            (float)($planet['crystal'] ?? 0) < $cost['crystal']
            ||
            (float)($planet['deuterium'] ?? 0) < $cost['deuterium']
        ) {
            $haveMetal =
                (float)($planet['metal'] ?? 0);

            $haveCrystal =
                (float)($planet['crystal'] ?? 0);

            $haveDeuterium =
                (float)($planet['deuterium'] ?? 0);

            $missingMetal = max(
                0,
                $cost['metal'] - $haveMetal
            );

            $missingCrystal = max(
                0,
                $cost['crystal'] - $haveCrystal
            );

            $missingDeuterium = max(
                0,
                $cost['deuterium'] - $haveDeuterium
            );

            /*
             * Neue Rohstofflogistik:
             * andere eigene Planeten nach Überschuss durchsuchen.
             */
            $transferStarted =
                BotAIResourceTransfer::requestForBuild(
                    (int)($planet['id_owner'] ?? 0),
                    $planet,
                    $missingMetal,
                    $missingCrystal,
                    $missingDeuterium
                );

            self::log([
                'action'    => 'NOT_ENOUGH_RESOURCES',
                'planet_id' => $planet['id'] ?? 0,
                'elementId' => $elementId,
                'field'     => $field,
                'level'     => $targetLevel,

                'need' => $cost,

                'missing' => [
                    'metal' =>
                        (int)$missingMetal,

                    'crystal' =>
                        (int)$missingCrystal,

                    'deuterium' =>
                        (int)$missingDeuterium,
                ],

                'have' => [
                    'metal' =>
                        $haveMetal,

                    'crystal' =>
                        $haveCrystal,

                    'deuterium' =>
                        $haveDeuterium,
                ],

                'transfer_started' =>
                    $transferStarted ? 1 : 0,
            ]);

            return false;
        }

        /* =========================
         * BAU STARTEN
         * ========================= */
        $now = time();
        $end = $now + 10; // TEST-dauer

        $queue = serialize([
            [
                $elementId,
                $targetLevel,
                [],
                (float)$end,
                'build'
            ]
        ]);

        Database::get()->update(
            "UPDATE " . DB_PREFIX . "planets
             SET
                metal         = metal - :m,
                crystal       = crystal - :c,
                deuterium     = deuterium - :d,
                b_building    = :end,
                b_building_id = :queue
             WHERE id = :pid",
            [
                ':m' =>
                    $cost['metal'],

                ':c' =>
                    $cost['crystal'],

                ':d' =>
                    $cost['deuterium'],

                ':end' =>
                    $end,

                ':queue' =>
                    $queue,

                ':pid' =>
                    $planet['id'],
            ]
        );

        self::log([
            'action'     => 'BUILD_STARTED',
            'planet_id'  => $planet['id'],
            'elementId'  => $elementId,
            'field'      => $field,
            'from'       => $levelBefore,
            'to'         => $targetLevel,
            'ends_at'    => $end,
        ]);

        return true;
    }

    /* =========================
     * COSTS FROM vars
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
            return [
                'metal' => 0,
                'crystal' => 0,
                'deuterium' => 0
            ];
        }

        $factor = (float)$v['factor'];

        return [
            'metal' =>
                (int)floor(
                    (float)$v['cost901']
                    * pow($factor, $level - 1)
                ),

            'crystal' =>
                (int)floor(
                    (float)$v['cost902']
                    * pow($factor, $level - 1)
                ),

            'deuterium' =>
                (int)floor(
                    (float)$v['cost903']
                    * pow($factor, $level - 1)
                ),
        ];
    }

    /* =========================
     * MAIN PLANET
     * ========================= */
    private static function getMainPlanet(int $uid): ?array
    {
        return Database::get()->selectSingle(
            "SELECT *
             FROM " . DB_PREFIX . "planets
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY id ASC
             LIMIT 1",
            [':uid' => $uid]
        );
    }

    /* =========================
     * LOGGING
     * ========================= */
    private static function log(array $data): void
    {
        $dir =
            ROOT_PATH . 'includes/ai_log/';

        if (!is_dir($dir)) {
            mkdir(
                $dir,
                0755,
                true
            );
        }

        $data['time'] =
            time();

        $data['datetime'] =
            date('Y-m-d H:i:s');

        file_put_contents(
            $dir . 'bot_actions.json',
            json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
            ) . PHP_EOL,
            FILE_APPEND
        );
    }

    /* =========================
     * BUILDING IDS
     * ========================= */
    private const ID_METAL_MINE      = 1;
    private const ID_CRYSTAL_MINE    = 2;
    private const ID_DEUTERIUM_SYNTH = 3;
    private const ID_SOLAR_PLANT     = 4;
    private const ID_ROBOT_FACTORY   = 14;
    private const ID_NANITE_FACTORY  = 15;
    private const ID_HANGAR          = 21;
    private const ID_METAL_STORE     = 22;
    private const ID_CRYSTAL_STORE   = 23;
    private const ID_DEUTERIUM_STORE = 24;
    private const ID_LABORATORY      = 31;
    private const ID_TERRAFORMER     = 33;
    private const ID_SILO            = 44;
}