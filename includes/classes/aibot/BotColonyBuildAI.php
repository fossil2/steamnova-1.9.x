<?php

class BotColonyBuildAI
{
    private const DEFENSE_ENABLED = true;

    private const ID_METAL_MINE      = 1;
    private const ID_CRYSTAL_MINE    = 2;
    private const ID_DEUTERIUM_SYNTH = 3;
    private const ID_SOLAR_PLANT     = 4;
    private const ID_FUSION_PLANT    = 12;
    private const ID_ROBOT_FACTORY   = 14;
    private const ID_HANGAR          = 21;
    private const ID_METAL_STORE     = 22;
    private const ID_CRYSTAL_STORE   = 23;
    private const ID_DEUTERIUM_STORE = 24;
    private const ID_TERRAFORMER     = 33;

    private const MAX_STORAGE_LEVEL = 10;
    private const MAX_FUSION_LEVEL  = 16;

    public static function run(int $userId): void
    {
        $db = Database::get();

        $USER = $db->selectSingle(
            "SELECT *
             FROM " . DB_PREFIX . "users
             WHERE id = :uid
               AND is_bot = 1",
            [':uid' => $userId]
        );

        if (!$USER) {
            self::log([
                'action' => 'NO_USER',
                'userId' => $userId,
            ]);
            return;
        }
        
        $USER['universe'] = (int)($USER['universe'] ?? 1);

        if (!isset($USER['factor']) || !is_array($USER['factor'])) {
            $USER['factor'] = [];
        }

        $USER['factor']['BuildTime']    = $USER['factor']['BuildTime'] ?? 0;
        $USER['factor']['ResearchTime'] = $USER['factor']['ResearchTime'] ?? 0;
        $USER['factor']['ShipTime']     = $USER['factor']['ShipTime'] ?? 0;
        $USER['factor']['ShipyardTime'] = $USER['factor']['ShipyardTime'] ?? 0;
        $USER['factor']['DefTime']      = $USER['factor']['DefTime'] ?? 0;

        $USER['BuildingTime']  = (float)($USER['BuildingTime'] ?? 0);
        $USER['ResearchTime']  = (float)($USER['ResearchTime'] ?? 0);
        $USER['ShipyardTime']  = (float)($USER['ShipyardTime'] ?? 0);
        $USER['DefensiveTime'] = (float)($USER['DefensiveTime'] ?? 0);

        $planets = $db->select(
            "SELECT *
             FROM " . DB_PREFIX . "planets
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY id ASC",
            [':uid' => $userId]
        );

        if (empty($planets)) {
            self::log([
                'action' => 'NO_PLANETS',
                'userId' => $userId,
            ]);
            return;
        }

        $mainPlanetId = self::getMainPlanetId($userId);

        $checkedColonies = 0;
        $startedBuilds   = 0;

        foreach ($planets as $PLANET) {
            if ((int)$PLANET['id'] === $mainPlanetId) {
                continue;
            }

            $checkedColonies++;

            $res = new ResourceUpdate();
            $res->CalcResource($USER, $PLANET, false);

            if ((int)$PLANET['b_building'] > time()) {
                self::log([
                    'action'    => 'QUEUE_ACTIVE',
                    'planet_id' => $PLANET['id'],
                    'until'     => $PLANET['b_building'],
                ]);
                continue;
            }

            $phase = self::getColonyPhase($PLANET);

            self::log([
                'action'    => 'COLONY_STATE',
                'planet_id' => $PLANET['id'],
                'phase'     => $phase,
                'metal'     => (int)($PLANET['metal_mine'] ?? 0),
                'crystal'   => (int)($PLANET['crystal_mine'] ?? 0),
                'deut'      => (int)($PLANET['deuterium_sintetizer'] ?? 0),
                'solar'     => (int)($PLANET['solar_plant'] ?? 0),
                'fusion'    => (int)($PLANET['fusion_plant'] ?? 0),
                'robot'     => (int)($PLANET['robot_factory'] ?? 0),
                'hangar'    => (int)($PLANET['hangar'] ?? 0),
            ]);

            if (self::tryBuild($PLANET, $phase)) {
                $startedBuilds++;
                continue;
            }

            if (self::DEFENSE_ENABLED && self::tryBuildDefense($PLANET, $phase)) {
                $startedBuilds++;
                continue;
            }

            self::log([
                'action'    => 'NO_ACTION_ON_COLONY',
                'planet_id' => $PLANET['id'],
                'phase'     => $phase,
            ]);
        }

        self::log([
            'action'          => 'RUN_FINISHED',
            'userId'          => $userId,
            'checkedColonies' => $checkedColonies,
            'startedBuilds'   => $startedBuilds,
        ]);
    }

    private static function getColonyPhase(array $p): string
    {
        $metal   = (int)($p['metal_mine'] ?? 0);
        $crystal = (int)($p['crystal_mine'] ?? 0);
        $deut    = (int)($p['deuterium_sintetizer'] ?? 0);
        $robot   = (int)($p['robot_factory'] ?? 0);

        if ($metal < 12 || $crystal < 9 || $deut < 6 || $robot < 3) {
            return 'bootstrap';
        }

        if ($metal < 20 || $crystal < 16 || $deut < 12 || $robot < 6) {
            return 'growth';
        }

        return 'mature';
    }

    private static function tryBuild(array $p, string $phase): bool
    {
        $buildOrder = self::getBuildOrder($phase);

        foreach ($buildOrder as $elementId) {
            $status = self::getBuildStatus($p, $elementId, $phase);

            if (!$status['allowed']) {
                continue;
            }

            if (self::startBuild($p, $elementId)) {
                self::log([
                    'action'    => 'COLONY_BUILD_STARTED',
                    'planet_id' => $p['id'],
                    'elementId' => $elementId,
                    'phase'     => $phase,
                    'reason'    => $status['reason'],
                ]);
                return true;
            }

            self::log([
                'action'    => 'COLONY_BUILD_FAILED',
                'planet_id' => $p['id'],
                'elementId' => $elementId,
                'phase'     => $phase,
                'reason'    => $status['reason'],
            ]);
        }

        return false;
    }

    private static function getBuildOrder(string $phase): array
    {
        return match ($phase) {
            'bootstrap' => [
                self::ID_SOLAR_PLANT,
                self::ID_METAL_MINE,
                self::ID_CRYSTAL_MINE,
                self::ID_DEUTERIUM_SYNTH,
                self::ID_ROBOT_FACTORY,
                self::ID_METAL_STORE,
                self::ID_CRYSTAL_STORE,
                self::ID_DEUTERIUM_STORE,
                self::ID_HANGAR,
            ],

            'growth' => [
                self::ID_SOLAR_PLANT,
                self::ID_METAL_MINE,
                self::ID_CRYSTAL_MINE,
                self::ID_DEUTERIUM_SYNTH,
                self::ID_FUSION_PLANT,
                self::ID_ROBOT_FACTORY,
                self::ID_METAL_STORE,
                self::ID_CRYSTAL_STORE,
                self::ID_DEUTERIUM_STORE,
                self::ID_HANGAR,
            ],

            default => [
                self::ID_SOLAR_PLANT,
                self::ID_METAL_MINE,
                self::ID_CRYSTAL_MINE,
                self::ID_DEUTERIUM_SYNTH,
                self::ID_FUSION_PLANT,
                self::ID_METAL_STORE,
                self::ID_CRYSTAL_STORE,
                self::ID_DEUTERIUM_STORE,
                self::ID_ROBOT_FACTORY,
                self::ID_HANGAR,
            ],
        };
    }

    private static function getBuildStatus(array $p, int $id, string $phase): array
    {
        $metalMine   = (int)($p['metal_mine'] ?? 0);
        $crystalMine = (int)($p['crystal_mine'] ?? 0);
        $deutSynth   = (int)($p['deuterium_sintetizer'] ?? 0);
        $solar       = (int)($p['solar_plant'] ?? 0);
        $fusion      = (int)($p['fusion_plant'] ?? 0);
        $robot       = (int)($p['robot_factory'] ?? 0);
        $hangar      = (int)($p['hangar'] ?? 0);

        $nextMineLevel = max($metalMine, $crystalMine, $deutSynth) + 1;
        $needsEnergy   = self::needsMoreEnergy($p, $nextMineLevel);

        if (
            $needsEnergy &&
            in_array($id, [
                self::ID_METAL_MINE,
                self::ID_CRYSTAL_MINE,
                self::ID_DEUTERIUM_SYNTH,
            ], true) &&
            !self::shouldPreferDeutMineForFusion($p, $phase)
        ) {
            return [
                'allowed' => false,
                'reason'  => 'energy_first',
            ];
        }

        $allowed = match ($phase) {
            'bootstrap' => match ($id) {
                self::ID_SOLAR_PLANT =>
                    $solar === 0 || $needsEnergy || $solar < 8,

                self::ID_FUSION_PLANT =>
                    false,

                self::ID_METAL_MINE =>
                    $metalMine < 12,

                self::ID_CRYSTAL_MINE =>
                    $crystalMine < 9,

                self::ID_DEUTERIUM_SYNTH =>
                    $deutSynth < 6,

                self::ID_METAL_STORE =>
                    (int)($p['metal_store'] ?? 0) < 2
                    || self::isStorageNeeded($p, 'metal'),

                self::ID_CRYSTAL_STORE =>
                    (int)($p['crystal_store'] ?? 0) < 2
                    || self::isStorageNeeded($p, 'crystal'),

                self::ID_DEUTERIUM_STORE =>
                    (int)($p['deuterium_store'] ?? 0) < 1
                    || self::isStorageNeeded($p, 'deuterium'),

                self::ID_ROBOT_FACTORY =>
                    $robot < 3,

                self::ID_HANGAR =>
                    $robot >= 2 && $hangar < 2,

                default => false,
            },

            'growth' => match ($id) {
                self::ID_SOLAR_PLANT =>
                    $needsEnergy,

                self::ID_FUSION_PLANT =>
                    $needsEnergy
                    && $fusion < self::MAX_FUSION_LEVEL
                    && $deutSynth >= ($fusion + 2)
                    && $metalMine >= 14
                    && $crystalMine >= 10,

                self::ID_METAL_MINE =>
                    $metalMine < 20
                    && $metalMine <= ($crystalMine + 3),

                self::ID_CRYSTAL_MINE =>
                    $crystalMine < 16
                    && $crystalMine <= ($metalMine + 1),

                self::ID_DEUTERIUM_SYNTH =>
                    $deutSynth < 12
                    || $deutSynth < ($fusion + 2),

                self::ID_METAL_STORE =>
                    (int)($p['metal_store'] ?? 0) < 4
                    || (
                        (int)($p['metal_store'] ?? 0) < self::MAX_STORAGE_LEVEL
                        && self::isStorageNeeded($p, 'metal')
                    ),

                self::ID_CRYSTAL_STORE =>
                    (int)($p['crystal_store'] ?? 0) < 3
                    || (
                        (int)($p['crystal_store'] ?? 0) < self::MAX_STORAGE_LEVEL
                        && self::isStorageNeeded($p, 'crystal')
                    ),

                self::ID_DEUTERIUM_STORE =>
                    (int)($p['deuterium_store'] ?? 0) < 2
                    || (
                        (int)($p['deuterium_store'] ?? 0) < self::MAX_STORAGE_LEVEL
                        && self::isStorageNeeded($p, 'deuterium')
                    ),

                self::ID_ROBOT_FACTORY =>
                    $robot < 6,

                self::ID_HANGAR =>
                    $robot >= 2 && $hangar < 4,

                default => false,
            },

            default => match ($id) {
                self::ID_SOLAR_PLANT =>
                    $needsEnergy,

                self::ID_FUSION_PLANT =>
                    $needsEnergy
                    && $fusion < self::MAX_FUSION_LEVEL
                    && $deutSynth >= ($fusion + 2),

                self::ID_METAL_MINE =>
                    $metalMine <= ($crystalMine + 4),

                self::ID_CRYSTAL_MINE =>
                    $crystalMine <= ($metalMine + 1),

                self::ID_DEUTERIUM_SYNTH =>
                    $deutSynth <= ($crystalMine - 1)
                    || $deutSynth < ($fusion + 2),

                self::ID_METAL_STORE =>
                    (int)($p['metal_store'] ?? 0) < self::MAX_STORAGE_LEVEL
                    && self::isStorageNeeded($p, 'metal'),

                self::ID_CRYSTAL_STORE =>
                    (int)($p['crystal_store'] ?? 0) < self::MAX_STORAGE_LEVEL
                    && self::isStorageNeeded($p, 'crystal'),

                self::ID_DEUTERIUM_STORE =>
                    (int)($p['deuterium_store'] ?? 0) < self::MAX_STORAGE_LEVEL
                    && self::isStorageNeeded($p, 'deuterium'),

                self::ID_ROBOT_FACTORY =>
                    $robot < 8,

                self::ID_HANGAR =>
                    $robot >= 2 && $hangar < 6,

                default => false,
            },
        };

        return [
            'allowed' => $allowed,
            'reason'  => $phase,
        ];
    }

    private static function shouldPreferDeutMineForFusion(array $p, string $phase): bool
    {
        $fusion    = (int)($p['fusion_plant'] ?? 0);
        $deutSynth = (int)($p['deuterium_sintetizer'] ?? 0);

        if ($phase === 'bootstrap') {
            return false;
        }

        return $fusion > 0 && $deutSynth < ($fusion + 2);
    }

    private static function tryBuildDefense(array $p, string $phase): bool
    {
        if ($phase === 'bootstrap') {
            return false;
        }

        if ((int)($p['hangar'] ?? 0) < 2) {
            return false;
        }

        $defIds = [401, 402, 403];

        foreach ($defIds as $id) {
            $limit = self::getDefenseLimit($id, $phase);
            if ($limit <= 0) {
                continue;
            }

            if (self::getDefenseCount($p, $id) >= $limit) {
                continue;
            }

            if (self::startDefenseBuild($p, $id)) {
                self::log([
                    'action'    => 'DEFENSE_BUILD_STARTED',
                    'planet_id' => $p['id'],
                    'elementId' => $id,
                    'phase'     => $phase,
                ]);
                return true;
            }
        }

        return false;
    }

    private static function getDefenseLimit(int $id, string $phase): int
    {
        return match ($phase) {
            'growth' => match ($id) {
                401 => 4,
                402 => 2,
                403 => 0,
                default => 0,
            },
            'mature' => match ($id) {
                401 => 8,
                402 => 4,
                403 => 2,
                default => 0,
            },
            default => 0,
        };
    }

    private static function getDefenseCount(array $planet, int $elementId): int
    {
        global $resource;

        if (!isset($resource[$elementId])) {
            return 0;
        }

        $field = $resource[$elementId];
        return (int)($planet[$field] ?? 0);
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
            (float)($planet['metal'] ?? 0)     < (float)($cost[901] ?? 0) ||
            (float)($planet['crystal'] ?? 0)   < (float)($cost[902] ?? 0) ||
            (float)($planet['deuterium'] ?? 0) < (float)($cost[903] ?? 0)
        ) {
            return false;
        }

        $now = time();
        $end = $now + 12;

        $queue = serialize([
            [$elementId, 1, [], (float)$end, 'build']
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

    private static function needsMoreEnergy(array $p, int $nextMineLevel): bool
    {
        $solarOutput  = max(0, (int)($p['solar_plant'] ?? 0)) * 55;
        $fusionOutput = max(0, (int)($p['fusion_plant'] ?? 0)) * 35;

        $currentNeed =
            ((int)($p['metal_mine'] ?? 0) * 10) +
            ((int)($p['crystal_mine'] ?? 0) * 15) +
            ((int)($p['deuterium_sintetizer'] ?? 0) * 25);

        $futureNeed = $currentNeed + ($nextMineLevel * 18);
        $required   = (int)ceil($futureNeed * 1.20);

        return ($solarOutput + $fusionOutput) < $required;
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

        return (float)($p[$res] ?? 0) > ($capacity * 0.95);
    }

    private static function startBuild(array $planet, int $elementId): bool
    {
        global $resource;

        if (!isset($resource[$elementId])) {
            self::log([
                'action'    => 'UNKNOWN_ELEMENT',
                'planet_id' => $planet['id'] ?? 0,
                'elementId' => $elementId,
            ]);
            return false;
        }

        $field       = $resource[$elementId];
        $levelBefore = (int)($planet[$field] ?? 0);
        $targetLevel = $levelBefore + 1;

        $cost = self::getBuildCost($elementId, $targetLevel);

        if (
            (float)($planet['metal'] ?? 0)     < $cost['metal'] ||
            (float)($planet['crystal'] ?? 0)   < $cost['crystal'] ||
            (float)($planet['deuterium'] ?? 0) < $cost['deuterium']
        ) {
            self::log([
                'action'    => 'NOT_ENOUGH_RESOURCES',
                'planet_id' => $planet['id'],
                'elementId' => $elementId,
                'field'     => $field,
                'target'    => $targetLevel,
                'need'      => $cost,
                'have'      => [
                    'metal'     => (float)($planet['metal'] ?? 0),
                    'crystal'   => (float)($planet['crystal'] ?? 0),
                    'deuterium' => (float)($planet['deuterium'] ?? 0),
                ],
            ]);
            return false;
        }

        $now = time();
        $end = $now + 10;

        $queue = serialize([
            [$elementId, $targetLevel, [], (float)$end, 'build']
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

    private static function getBuildCost(int $id, int $level): array
    {
        $v = Database::get()->selectSingle(
            "SELECT factor, cost901, cost902, cost903
             FROM " . DB_PREFIX . "vars
             WHERE elementID = :id",
            [':id' => $id]
        );

        if (!$v) {
            return ['metal' => 0, 'crystal' => 0, 'deuterium' => 0];
        }

        $factor = (float)$v['factor'];

        return [
            'metal'     => (int)floor((float)$v['cost901'] * pow($factor, $level - 1)),
            'crystal'   => (int)floor((float)$v['cost902'] * pow($factor, $level - 1)),
            'deuterium' => (int)floor((float)$v['cost903'] * pow($factor, $level - 1)),
        ];
    }

    private static function getMainPlanetId(int $uid): int
    {
        $main = Database::get()->selectSingle(
            "SELECT id
             FROM " . DB_PREFIX . "planets
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY id ASC
             LIMIT 1",
            [':uid' => $uid]
        );

        return (int)($main['id'] ?? 0);
    }

    private static function log(array $data): void
    {
        $dir = ROOT_PATH . 'includes/ai_log/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data['time']     = time();
        $data['datetime'] = date('Y-m-d H:i:s');

        file_put_contents(
            $dir . 'bot_colony_actions.json',
            json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );
    }
}