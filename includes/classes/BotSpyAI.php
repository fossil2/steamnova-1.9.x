<?php
declare(strict_types=1);

class BotSpyAI
{
    /* =========================
     * CONFIG
     * ========================= */
    private const DEBUG = true;

    private const MIN_HANGAR     = 4;
    private const MIN_COMBUSTION = 3;
    private const MIN_SPY_TECH   = 2;

    private const MIN_SONDEN = 1;

    // IDs wie in deinem Spiel
    private const SHIP_SMALL_CARGO   = 202;
    private const SHIP_LARGE_CARGO   = 203;
    private const SHIP_LIGHT_FIGHTER = 204;
    private const SHIP_HEAVY_FIGHTER = 205;
    private const SHIP_CRUISER       = 206;
    private const SHIP_BATTLESHIP    = 207;
    private const SHIP_SPY           = 210;
    private const SHIP_DESTROYER     = 213;
    private const SHIP_BATTLECRUISER = 215;

    private const EXPEDITION_SHIPS  = 4;
    private const EXPEDITION_PLANET = 16;

    private const MIN_COMBAT_SHIPS    = 5;
    private const MAX_ATTACK_DEFENSE  = 50;
    private const MIN_ATTACK_COOLDOWN = 7200;
    private const MAX_ATTACK_COOLDOWN = 21600;
    private const MAX_ATTACK_RANGE    = 100;
    private const INACTIVE_AFTER      = 604800; // 7 Tage

    private const MIN_ATTACK_LIGHT_FIGHTERS        = 20;
    private const ATTACK_BUILD_BATCH_LIGHT_FIGHTER = 10;

    // Dynamische Flottenaufteilung
    private const ATTACK_USE_PERCENT_LF = 0.35;
    private const ATTACK_USE_PERCENT_HF = 0.35;
    private const ATTACK_USE_PERCENT_CR = 0.40;
    private const ATTACK_USE_PERCENT_BS = 0.45;
    private const ATTACK_USE_PERCENT_DS = 0.50;
    private const ATTACK_USE_PERCENT_BC = 0.45;

    private const ATTACK_HOME_RESERVE_LF = 20;
    private const ATTACK_HOME_RESERVE_HF = 6;
    private const ATTACK_HOME_RESERVE_CR = 3;
    private const ATTACK_HOME_RESERVE_BS = 2;
    private const ATTACK_HOME_RESERVE_DS = 1;
    private const ATTACK_HOME_RESERVE_BC = 1;

    private const ATTACK_MAX_SMALL_CARGO = 20;
    private const ATTACK_MAX_LARGE_CARGO = 12;

    /* =========================
     * DEBUG
     * ========================= */
    private static function log(string $msg, array $data = []): void
    {
        if (!self::DEBUG) {
            return;
        }

        $line = '[BotSpyAI] ' . $msg;

        if (!empty($data)) {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json !== false) {
                $line .= ' | ' . $json;
            }
        }

        error_log($line);
    }

    /* =========================
     * ENTRY
     * ========================= */
    public static function run(int $botId): void
    {
        $db = Database::get();

        self::log('RUN start', ['botId' => $botId]);

        $USER = $db->selectSingle(
            'SELECT * FROM %%USERS%% WHERE id = :id AND is_bot = 1;',
            [':id' => $botId]
        );

        if (!$USER) {
            self::log('RUN no bot user found', ['botId' => $botId]);
            return;
        }

        $USER = self::normalizeUserFactors($USER);

        $PLANETS = $db->select(
            'SELECT *
             FROM %%PLANETS%%
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY galaxy ASC, system ASC, planet ASC;',
            [':uid' => $botId]
        );

        if (empty($PLANETS) || !is_array($PLANETS)) {
            self::log('RUN no planets found', ['botId' => $botId]);
            return;
        }

        self::log('RUN planets loaded', [
            'userId'      => $USER['id'],
            'planetCount' => count($PLANETS),
        ]);

        // Spionage / Expo auf allen Planeten
        foreach ($PLANETS as $PLANET) {
            self::log('RUN process planet', [
                'userId'   => $USER['id'],
                'planetId' => $PLANET['id'],
                'galaxy'   => $PLANET['galaxy'] ?? null,
                'system'   => $PLANET['system'] ?? null,
                'planet'   => $PLANET['planet'] ?? null,
            ]);

            self::handleSpy($USER, $PLANET);
            self::handleExpedition($USER, $PLANET);
        }

        // Angriff bevorzugt von Planeten mit fertigen Kampfschiffen
        usort($PLANETS, static function (array $a, array $b): int {
            $combatA = self::getPlanetCombatShips($a);
            $combatB = self::getPlanetCombatShips($b);
            return $combatB <=> $combatA;
        });

        foreach ($PLANETS as $PLANET) {
            self::handleAttack($USER, $PLANET);

            $freshUser = $db->selectSingle(
                'SELECT * FROM %%USERS%% WHERE id = :id AND is_bot = 1;',
                [':id' => $botId]
            );

            if (is_array($freshUser)) {
                $USER = self::normalizeUserFactors($freshUser);
            }

            if (!empty($USER['bot_next_attack']) && $USER['bot_next_attack'] > TIMESTAMP) {
                break;
            }
        }

        self::log('RUN finished', [
            'userId'      => $USER['id'],
            'planetCount' => count($PLANETS),
        ]);
    }

    /* =========================
     * USER FACTORS FIX
     * ========================= */
    private static function normalizeUserFactors(array $USER): array
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

    /* =========================
     * 🛰 SPIONAGE
     * ========================= */
    private static function handleSpy(array $USER, array $PLANET): void
    {
        self::log('SPY start', [
            'userId'   => $USER['id'],
            'planetId' => $PLANET['id'],
        ]);

        if (!empty($USER['bot_next_spy']) && $USER['bot_next_spy'] > TIMESTAMP) {
            self::log('SPY cooldown active', [
                'next' => $USER['bot_next_spy'],
                'now'  => TIMESTAMP,
            ]);
            return;
        }

        if (
            (int)($PLANET['hangar'] ?? 0) < self::MIN_HANGAR ||
            (int)($USER['combustion_tech'] ?? 0) < self::MIN_COMBUSTION ||
            (int)($USER['spy_tech'] ?? 0) < self::MIN_SPY_TECH
        ) {
            self::log('SPY requirements not met', [
                'hangar'          => $PLANET['hangar'] ?? 0,
                'combustion_tech' => $USER['combustion_tech'] ?? 0,
                'spy_tech'        => $USER['spy_tech'] ?? 0,
            ]);
            return;
        }

        if ((int)($PLANET['spy_sonde'] ?? 0) < self::MIN_SONDEN) {
            self::log('SPY queue sondes', [
                'planetId' => $PLANET['id'],
                'have'     => $PLANET['spy_sonde'] ?? 0,
                'need'     => self::MIN_SONDEN,
            ]);
            self::queueShip((int)$PLANET['id'], self::SHIP_SPY, 1);
            return;
        }

        $TARGET = self::getSpyTarget($USER, $PLANET);

        self::log('SPY target', is_array($TARGET) ? [
            'targetPlanetId' => $TARGET['id'] ?? null,
            'targetOwner'    => $TARGET['id_owner'] ?? null,
            'galaxy'         => $TARGET['galaxy'] ?? null,
            'system'         => $TARGET['system'] ?? null,
            'planet'         => $TARGET['planet'] ?? null,
        ] : []);

        if (!$TARGET) {
            self::log('SPY no target found');
            return;
        }

        self::sendSpyFleet($USER, $PLANET, $TARGET);

        Database::get()->update(
            'UPDATE %%USERS%% SET bot_next_spy = :t WHERE id = :id;',
            [
                ':t'  => TIMESTAMP + mt_rand(6 * 3600, 18 * 3600),
                ':id' => $USER['id'],
            ]
        );

        self::log('SPY cooldown set', ['userId' => $USER['id']]);
    }

    /* =========================
     * 🚀 EXPEDITION
     * ========================= */
    private static function handleExpedition(array $USER, array $PLANET): void
    {
        global $resource;

        $USER = self::normalizeUserFactors($USER);

        self::log('EXPO start', [
            'userId'   => $USER['id'],
            'planetId' => $PLANET['id'],
        ]);

        if (empty($resource[124]) || empty($USER[$resource[124]]) || (int)$USER[$resource[124]] < 1) {
            self::log('EXPO no astrophysics / expedition tech', [
                'resource124' => $resource[124] ?? null,
                'value'       => isset($resource[124], $USER[$resource[124]]) ? $USER[$resource[124]] : null,
            ]);
            return;
        }

        if (!empty($USER['bot_next_expedition']) && $USER['bot_next_expedition'] > TIMESTAMP) {
            self::log('EXPO cooldown active', [
                'next' => $USER['bot_next_expedition'],
                'now'  => TIMESTAMP,
            ]);
            return;
        }

        require_once ROOT_PATH . 'includes/classes/class.FleetFunctions.php';

        $haveCargo    = (int)($PLANET['small_ship_cargo'] ?? 0);
        $queuedCargo  = self::getQueuedAmount((int)$PLANET['id'], self::SHIP_SMALL_CARGO);
        $plannedCargo = $haveCargo + $queuedCargo;

        self::log('EXPO cargo check', [
            'planetId' => $PLANET['id'],
            'have'     => $haveCargo,
            'queued'   => $queuedCargo,
            'planned'  => $plannedCargo,
            'need'     => self::EXPEDITION_SHIPS,
        ]);

        if ($plannedCargo < self::EXPEDITION_SHIPS) {
            $missing = self::EXPEDITION_SHIPS - $plannedCargo;

            self::queueShip(
                (int)$PLANET['id'],
                self::SHIP_SMALL_CARGO,
                $missing
            );

            self::log('EXPO queued cargos', [
                'planetId' => $PLANET['id'],
                'amount'   => $missing,
            ]);
            return;
        }

        if ($haveCargo < self::EXPEDITION_SHIPS) {
            self::log('EXPO ships still in production', [
                'planetId' => $PLANET['id'],
                'have'     => $haveCargo,
                'queued'   => $queuedCargo,
                'need'     => self::EXPEDITION_SHIPS,
            ]);
            return;
        }

        $currentFleets = FleetFunctions::GetCurrentFleets($USER['id']);
        $maxFleets     = FleetFunctions::GetMaxFleetSlots($USER);

        self::log('EXPO fleet slots', [
            'current' => $currentFleets,
            'max'     => $maxFleets,
        ]);

        if ($currentFleets >= $maxFleets) {
            self::log('EXPO no fleet slot free');
            return;
        }

        $fleetArray = [
            self::SHIP_SMALL_CARGO => self::EXPEDITION_SHIPS,
        ];

        $distance = FleetFunctions::GetTargetDistance(
            [$PLANET['galaxy'], $PLANET['system'], $PLANET['planet']],
            [$PLANET['galaxy'], $PLANET['system'], self::EXPEDITION_PLANET]
        );

        $speed = FleetFunctions::GetFleetMaxSpeed($fleetArray, $USER);

        $fleetStartTime = TIMESTAMP;
        $fleetDuration  = (int)FleetFunctions::GetMissionDuration(
            10,
            $speed,
            $distance,
            FleetFunctions::GetGameSpeedFactor(),
            $USER
        );

        $haltSpeed = Config::get()->halt_speed ?? 1;
        $stayHours = 1;
        $fleetStayTime = TIMESTAMP + $fleetDuration + (int)($stayHours * 3600 / $haltSpeed);
        $fleetEndTime  = $fleetStayTime + $fleetDuration;

        self::log('EXPO sendFleet', [
            'planetId'   => $PLANET['id'],
            'fleetArray' => $fleetArray,
            'distance'   => $distance,
            'speed'      => $speed,
            'duration'   => $fleetDuration,
        ]);

        FleetFunctions::sendFleet(
            $fleetArray,
            15,
            $USER['id'],
            $PLANET['id'],
            $PLANET['galaxy'],
            $PLANET['system'],
            $PLANET['planet'],
            1,
            0,
            0,
            $PLANET['galaxy'],
            $PLANET['system'],
            self::EXPEDITION_PLANET,
            1,
            [901 => 0, 902 => 0, 903 => 0],
            $fleetStartTime,
            $fleetStayTime,
            $fleetEndTime
        );

        Database::get()->update(
            'UPDATE %%USERS%% SET bot_next_expedition = :t WHERE id = :id;',
            [
                ':t'  => TIMESTAMP + mt_rand(3600, 10800),
                ':id' => $USER['id'],
            ]
        );

        self::log('EXPO sent', [
            'userId'   => $USER['id'],
            'planetId' => $PLANET['id'],
        ]);
    }

    /* =========================
     * ⚔ ANGRIFF
     * ========================= */
    private static function handleAttack(array $USER, array $PLANET): void
    {
        $USER = self::normalizeUserFactors($USER);

        self::log('ATTACK start', [
            'user'   => $USER['id'],
            'planet' => $PLANET['id'],
        ]);

        if (!empty($USER['bot_next_attack']) && $USER['bot_next_attack'] > TIMESTAMP) {
            self::log('ATTACK cooldown active', [
                'next' => $USER['bot_next_attack'],
                'now'  => TIMESTAMP,
            ]);
            return;
        }

        require_once ROOT_PATH . 'includes/classes/class.FleetFunctions.php';

        $current = FleetFunctions::GetCurrentFleets($USER['id']);
        $max     = FleetFunctions::GetMaxFleetSlots($USER);

        self::log('ATTACK fleet slots', [
            'current' => $current,
            'max'     => $max,
        ]);

        if ($current >= $max) {
            self::log('ATTACK no free fleet slot');
            return;
        }

        if (!self::ensureAttackShips($PLANET)) {
            self::log('ATTACK waiting for combat ships to finish', [
                'planetId' => $PLANET['id'],
            ]);
            return;
        }

        $TARGET = self::getAttackTarget($USER, $PLANET);

        self::log('ATTACK target', is_array($TARGET) ? [
            'targetPlanetId' => $TARGET['id'] ?? null,
            'targetOwner'    => $TARGET['id_owner'] ?? null,
            'galaxy'         => $TARGET['galaxy'] ?? null,
            'system'         => $TARGET['system'] ?? null,
            'planet'         => $TARGET['planet'] ?? null,
            'defense_count'  => $TARGET['defense_count'] ?? null,
            'onlinetime'     => $TARGET['onlinetime'] ?? null,
        ] : []);

        if (!$TARGET) {
            self::log('ATTACK no target found');
            return;
        }

        $fleetArray = self::buildAttackFleet($PLANET, $TARGET);
        self::log('ATTACK fleet built', $fleetArray);

        if (empty($fleetArray)) {
            self::log('ATTACK no fleet available');
            return;
        }

        self::sendAttackFleet($USER, $PLANET, $TARGET, $fleetArray);

        Database::get()->update(
            'UPDATE %%USERS%% SET bot_next_attack = :t WHERE id = :id;',
            [
                ':t'  => TIMESTAMP + mt_rand(self::MIN_ATTACK_COOLDOWN, self::MAX_ATTACK_COOLDOWN),
                ':id' => $USER['id'],
            ]
        );

        self::log('ATTACK cooldown set', ['userId' => $USER['id']]);
    }

    private static function ensureAttackShips(array $PLANET): bool
    {
        $hangar = (int)($PLANET['hangar'] ?? 0);
        if ($hangar < self::MIN_HANGAR) {
            self::log('ATTACK no attack ship build possible yet', [
                'planetId' => $PLANET['id'],
                'hangar'   => $hangar,
                'need'     => self::MIN_HANGAR,
            ]);
            return false;
        }

        $ships    = self::getAttackShipSet($PLANET);
        $haveLF   = $ships['LF'];
        $queuedLF = self::getQueuedAmount((int)$PLANET['id'], self::SHIP_LIGHT_FIGHTER);
        $totalLF  = $haveLF + $queuedLF;

        self::log('ATTACK ensure ships', [
            'planetId' => $PLANET['id'] ?? null,
            'haveLF'   => $haveLF,
            'queuedLF' => $queuedLF,
            'totalLF'  => $totalLF,
            'needLF'   => self::MIN_ATTACK_LIGHT_FIGHTERS,
        ]);

        if ($haveLF >= self::MIN_ATTACK_LIGHT_FIGHTERS) {
            return true;
        }

        if ($totalLF < self::MIN_ATTACK_LIGHT_FIGHTERS) {
            $missing = min(
                self::ATTACK_BUILD_BATCH_LIGHT_FIGHTER,
                self::MIN_ATTACK_LIGHT_FIGHTERS - $totalLF
            );

            if ($missing > 0) {
                self::queueShip((int)$PLANET['id'], self::SHIP_LIGHT_FIGHTER, $missing);

                self::log('ATTACK queued light fighters', [
                    'planetId' => $PLANET['id'],
                    'amount'   => $missing,
                ]);
            }
        } else {
            self::log('ATTACK ships still in production', [
                'planetId' => $PLANET['id'],
                'haveLF'   => $haveLF,
                'queuedLF' => $queuedLF,
            ]);
        }

        return false;
    }

    private static function getAttackShipSet(array $PLANET): array
    {
        return [
            'LF'  => (int)($PLANET['light_hunter'] ?? 0),     // 204
            'HF'  => (int)($PLANET['heavy_hunter'] ?? 0),     // 205
            'CR'  => (int)($PLANET['crusher'] ?? 0),          // 206
            'BS'  => (int)($PLANET['battle_ship'] ?? 0),      // 207
            'DS'  => (int)($PLANET['destructor'] ?? 0),       // 213
            'BC'  => (int)($PLANET['battleship'] ?? 0),       // 215
            'SC'  => (int)($PLANET['small_ship_cargo'] ?? 0), // 202
            'LC'  => (int)($PLANET['big_ship_cargo'] ?? 0),   // 203
            'SPY' => (int)($PLANET['spy_sonde'] ?? 0),        // 210
        ];
    }

    private static function getAttackShipAmount(int $have, float $percent, int $reserve): int
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

    private static function getDefenseFactor(?array $TARGET): float
    {
        $def = (int)($TARGET['defense_count'] ?? 0);

        if ($def <= 0) {
            return 0.55;
        }
        if ($def <= 5) {
            return 0.70;
        }
        if ($def <= 15) {
            return 0.85;
        }
        if ($def <= 30) {
            return 1.00;
        }

        return 1.15;
    }

    private static function buildAttackFleet(array $PLANET, ?array $TARGET = null): array
    {
        $fleet = [];
        $ships = self::getAttackShipSet($PLANET);
        $defFactor = self::getDefenseFactor($TARGET);

        self::log('ATTACK ships detected', array_merge(
            [
                'planetId'  => $PLANET['id'] ?? null,
                'defFactor' => $defFactor,
            ],
            $ships
        ));

        $sendLF = (int)floor(
            self::getAttackShipAmount(
                $ships['LF'],
                self::ATTACK_USE_PERCENT_LF,
                self::ATTACK_HOME_RESERVE_LF
            ) * $defFactor
        );

        $sendHF = (int)floor(
            self::getAttackShipAmount(
                $ships['HF'],
                self::ATTACK_USE_PERCENT_HF,
                self::ATTACK_HOME_RESERVE_HF
            ) * $defFactor
        );

        $sendCR = (int)floor(
            self::getAttackShipAmount(
                $ships['CR'],
                self::ATTACK_USE_PERCENT_CR,
                self::ATTACK_HOME_RESERVE_CR
            ) * $defFactor
        );

        $sendBS = (int)floor(
            self::getAttackShipAmount(
                $ships['BS'],
                self::ATTACK_USE_PERCENT_BS,
                self::ATTACK_HOME_RESERVE_BS
            ) * $defFactor
        );

        $sendDS = (int)floor(
            self::getAttackShipAmount(
                $ships['DS'],
                self::ATTACK_USE_PERCENT_DS,
                self::ATTACK_HOME_RESERVE_DS
            ) * $defFactor
        );

        $sendBC = (int)floor(
            self::getAttackShipAmount(
                $ships['BC'],
                self::ATTACK_USE_PERCENT_BC,
                self::ATTACK_HOME_RESERVE_BC
            ) * $defFactor
        );

        $usableLF = max(0, $ships['LF'] - self::ATTACK_HOME_RESERVE_LF);
        $usableHF = max(0, $ships['HF'] - self::ATTACK_HOME_RESERVE_HF);
        $usableCR = max(0, $ships['CR'] - self::ATTACK_HOME_RESERVE_CR);
        $usableBS = max(0, $ships['BS'] - self::ATTACK_HOME_RESERVE_BS);
        $usableDS = max(0, $ships['DS'] - self::ATTACK_HOME_RESERVE_DS);
        $usableBC = max(0, $ships['BC'] - self::ATTACK_HOME_RESERVE_BC);

        $sendLF = min($sendLF, $usableLF);
        $sendHF = min($sendHF, $usableHF);
        $sendCR = min($sendCR, $usableCR);
        $sendBS = min($sendBS, $usableBS);
        $sendDS = min($sendDS, $usableDS);
        $sendBC = min($sendBC, $usableBC);

        if ($sendLF > 0) {
            $fleet[self::SHIP_LIGHT_FIGHTER] = $sendLF;
        }
        if ($sendHF > 0) {
            $fleet[self::SHIP_HEAVY_FIGHTER] = $sendHF;
        }
        if ($sendCR > 0) {
            $fleet[self::SHIP_CRUISER] = $sendCR;
        }
        if ($sendBS > 0) {
            $fleet[self::SHIP_BATTLESHIP] = $sendBS;
        }
        if ($sendDS > 0) {
            $fleet[self::SHIP_DESTROYER] = $sendDS;
        }
        if ($sendBC > 0) {
            $fleet[self::SHIP_BATTLECRUISER] = $sendBC;
        }

        $combatShips =
            ($fleet[self::SHIP_LIGHT_FIGHTER] ?? 0) +
            ($fleet[self::SHIP_HEAVY_FIGHTER] ?? 0) +
            ($fleet[self::SHIP_CRUISER] ?? 0) +
            ($fleet[self::SHIP_BATTLESHIP] ?? 0) +
            ($fleet[self::SHIP_DESTROYER] ?? 0) +
            ($fleet[self::SHIP_BATTLECRUISER] ?? 0);

        self::log('ATTACK combatShips', [
            'planetId' => $PLANET['id'] ?? null,
            'count'    => $combatShips,
            'min'      => self::MIN_COMBAT_SHIPS,
        ]);

        if ($combatShips < self::MIN_COMBAT_SHIPS) {
            return [];
        }

        $sendLC = min($ships['LC'], self::ATTACK_MAX_LARGE_CARGO);
        $sendSC = min($ships['SC'], self::ATTACK_MAX_SMALL_CARGO);

        if ($sendLC > 0) {
            $fleet[self::SHIP_LARGE_CARGO] = $sendLC;
        } elseif ($sendSC > 0) {
            $fleet[self::SHIP_SMALL_CARGO] = $sendSC;
        }

        self::log('ATTACK fleet composition', [
            'planetId' => $PLANET['id'] ?? null,
            'fleet'    => $fleet,
        ]);

        return $fleet;
    }

    private static function getPlanetCombatShips(array $PLANET): int
    {
        $ships = self::getAttackShipSet($PLANET);

        return
            $ships['LF'] +
            $ships['HF'] +
            $ships['CR'] +
            $ships['BS'] +
            $ships['DS'] +
            $ships['BC'];
    }

    private static function getAttackTarget(array $USER, array $PLANET): ?array
    {
        $row = Database::get()->selectSingle(
            'SELECT
                p.*,
                u.username,
                u.onlinetime,
                up.total_points,
                (
                    COALESCE(p.misil_launcher, 0) +
                    COALESCE(p.small_laser, 0) +
                    COALESCE(p.big_laser, 0) +
                    COALESCE(p.gauss_canyon, 0) +
                    COALESCE(p.ionic_canyon, 0) +
                    COALESCE(p.buster_canyon, 0) +
                    COALESCE(p.small_protection_shield, 0) +
                    COALESCE(p.big_protection_shield, 0)
                ) AS defense_count
             FROM %%PLANETS%% p
             INNER JOIN %%USERS%% u ON u.id = p.id_owner
             LEFT JOIN %%USER_POINTS%% up ON up.id_owner = u.id
             WHERE p.planet_type = 1
               AND p.id_owner != :me
               AND u.is_bot = 0
               AND (u.urlaubs_modus = 0 OR u.urlaubs_modus IS NULL)
               AND p.galaxy = :galaxy
               AND p.system BETWEEN :s1 AND :s2
               AND (
               -- Inaktive immer angreifen
                  u.onlinetime < :inactiveTime

                 OR

               (
        -- Nur Spieler mit genug Punkten
        COALESCE(up.total_points, 0) >= 5000

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
        ) <= :maxDefense
    )
)
             ORDER BY
                CASE WHEN u.onlinetime < :inactiveTime2 THEN 0 ELSE 1 END ASC,
                defense_count ASC,
                RAND()
             LIMIT 1;',
            [
                ':me'            => $USER['id'],
                ':galaxy'        => $PLANET['galaxy'],
                ':s1'            => max(1, (int)$PLANET['system'] - self::MAX_ATTACK_RANGE),
                ':s2'            => min(499, (int)$PLANET['system'] + self::MAX_ATTACK_RANGE),
                ':inactiveTime'  => TIMESTAMP - self::INACTIVE_AFTER,
                ':inactiveTime2' => TIMESTAMP - self::INACTIVE_AFTER,
                ':maxDefense'    => self::MAX_ATTACK_DEFENSE,
            ]
        );

        self::log('ATTACK query result', is_array($row) ? [
            'targetPlanetId' => $row['id'] ?? null,
            'targetOwner'    => $row['id_owner'] ?? null,
            'defense_count'  => $row['defense_count'] ?? null,
            'onlinetime'     => $row['onlinetime'] ?? null,
            'username'       => $row['username'] ?? null,
        ] : []);

        return is_array($row) ? $row : null;
    }

    private static function sanitizeFleetByCurrentPlanet(array $PLANET, array $fleetArray): array
    {
        $available = [
            self::SHIP_SMALL_CARGO   => (int)($PLANET['small_ship_cargo'] ?? 0),
            self::SHIP_LARGE_CARGO   => (int)($PLANET['big_ship_cargo'] ?? 0),
            self::SHIP_LIGHT_FIGHTER => (int)($PLANET['light_hunter'] ?? 0),
            self::SHIP_HEAVY_FIGHTER => (int)($PLANET['heavy_hunter'] ?? 0),
            self::SHIP_CRUISER       => (int)($PLANET['crusher'] ?? 0),
            self::SHIP_BATTLESHIP    => (int)($PLANET['battle_ship'] ?? 0),
            self::SHIP_SPY           => (int)($PLANET['spy_sonde'] ?? 0),
            self::SHIP_DESTROYER     => (int)($PLANET['destructor'] ?? 0),
            self::SHIP_BATTLECRUISER => (int)($PLANET['battleship'] ?? 0),
        ];

        foreach ($fleetArray as $shipId => $amount) {
            $amount = (int)$amount;
            $have   = (int)($available[$shipId] ?? 0);

            if ($amount > $have) {
                self::log('ATTACK fleet sanitize reduce', [
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

    private static function countCombatShipsInFleet(array $fleetArray): int
    {
        return
            (int)($fleetArray[self::SHIP_LIGHT_FIGHTER] ?? 0) +
            (int)($fleetArray[self::SHIP_HEAVY_FIGHTER] ?? 0) +
            (int)($fleetArray[self::SHIP_CRUISER] ?? 0) +
            (int)($fleetArray[self::SHIP_BATTLESHIP] ?? 0) +
            (int)($fleetArray[self::SHIP_DESTROYER] ?? 0) +
            (int)($fleetArray[self::SHIP_BATTLECRUISER] ?? 0);
    }

    private static function sendAttackFleet(array $USER, array $PLANET, array $TARGET, array $fleetArray): void
    {
        $USER = self::normalizeUserFactors($USER);

        require_once ROOT_PATH . 'includes/classes/class.FleetFunctions.php';

        $freshPlanet = Database::get()->selectSingle(
            'SELECT * FROM %%PLANETS%% WHERE id = :id LIMIT 1;',
            [
                ':id' => $PLANET['id'],
            ]
        );

        if (!$freshPlanet || !is_array($freshPlanet)) {
            self::log('ATTACK sendFleet planet reload failed', [
                'planetId' => $PLANET['id'] ?? null,
            ]);
            return;
        }

        $PLANET = $freshPlanet;

        $fleetArray = self::sanitizeFleetByCurrentPlanet($PLANET, $fleetArray);

        self::log('ATTACK fleet after sanitize', [
            'planetId' => $PLANET['id'],
            'fleet'    => $fleetArray,
        ]);

        $combatShips = self::countCombatShipsInFleet($fleetArray);
        if ($combatShips < self::MIN_COMBAT_SHIPS) {
            self::log('ATTACK abort after sanitize, too few combat ships', [
                'planetId'    => $PLANET['id'],
                'combatShips' => $combatShips,
                'min'         => self::MIN_COMBAT_SHIPS,
            ]);
            return;
        }

        if (empty($fleetArray)) {
            self::log('ATTACK abort after sanitize, empty fleet', [
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

        self::log('ATTACK sendFleet', [
            'fromPlanetId' => $PLANET['id'],
            'fleetArray'   => $fleetArray,
            'distance'     => $distance,
            'speed'        => $speed,
            'duration'     => $duration,
            'targetId'     => $TARGET['id'] ?? null,
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

    /* =========================
     * FLEETS
     * ========================= */
    private static function sendSpyFleet(array $USER, array $PLANET, array $TARGET): void
    {
        $USER = self::normalizeUserFactors($USER);

        require_once ROOT_PATH . 'includes/classes/class.FleetFunctions.php';

        $fleetArray = [self::SHIP_SPY => 1];

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

        self::log('SPY sendFleet', [
            'fromPlanetId' => $PLANET['id'],
            'distance'     => $distance,
            'speed'        => $speed,
            'duration'     => $duration,
            'targetId'     => $TARGET['id'] ?? null,
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

    /* =========================
     * HELFER
     * ========================= */
    private static function queueShip(int $planetId, int $shipId, int $amount): void
    {
        if ($amount <= 0) {
            self::log('QUEUE skip amount <= 0', [
                'planetId' => $planetId,
                'shipId'   => $shipId,
                'amount'   => $amount,
            ]);
            return;
        }

        $db = Database::get();

        $planet = $db->selectSingle(
            'SELECT b_hangar_id FROM %%PLANETS%% WHERE id = :id;',
            [
                ':id' => $planetId,
            ]
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
            if (
                is_array($entry) &&
                isset($entry[0], $entry[1]) &&
                (int)$entry[0] === $shipId
            ) {
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

        self::log('QUEUE updated', [
            'planetId' => $planetId,
            'shipId'   => $shipId,
            'amount'   => $amount,
            'queue'    => $queue,
        ]);
    }

    private static function getQueuedAmount(int $planetId, int $shipId): int
    {
        $planet = Database::get()->selectSingle(
            'SELECT b_hangar_id FROM %%PLANETS%% WHERE id = :id;',
            [
                ':id' => $planetId,
            ]
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
            if (
                is_array($entry) &&
                isset($entry[0], $entry[1]) &&
                (int)$entry[0] === $shipId
            ) {
                $amount += (int)$entry[1];
            }
        }

        self::log('QUEUE amount', [
            'planetId' => $planetId,
            'shipId'   => $shipId,
            'amount'   => $amount,
        ]);

        return $amount;
    }

    private static function getSpyTarget(array $USER, array $PLANET): ?array
    {
        $row = Database::get()->selectSingle(
            'SELECT p.*
             FROM %%PLANETS%% p
             JOIN %%USERS%% u ON u.id = p.id_owner
             WHERE u.is_bot = 0
               AND p.id_owner != :me
               AND p.planet_type = 1
               AND p.galaxy = :gal
               AND p.system BETWEEN :s1 AND :s2
             ORDER BY RAND()
             LIMIT 1;',
            [
                ':me'  => $USER['id'],
                ':gal' => $PLANET['galaxy'],
                ':s1'  => max(1, (int)$PLANET['system'] - 100),
                ':s2'  => min(499, (int)$PLANET['system'] + 100),
            ]
        );

        return is_array($row) ? $row : null;
    }
}