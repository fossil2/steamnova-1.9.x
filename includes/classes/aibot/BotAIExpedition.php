<?php
declare(strict_types=1);

require_once ROOT_PATH . 'includes/classes/aibot/BotAICommon.php';

class BotAIExpedition extends BotAICommon
{
    private const EXPO_MIN_CARGO_EQUIV = 4;

    private const EXPO_RESERVE_SC  = 0;
    private const EXPO_RESERVE_LC  = 1;
    private const EXPO_RESERVE_SPY = 1;
    private const EXPO_RESERVE_LF  = 1;
    private const EXPO_RESERVE_HF  = 1;
    private const EXPO_RESERVE_CR  = 0;
    private const EXPO_RESERVE_BS  = 0;

    public static function runPlanet(array $USER, array $PLANET): bool
    {
        if (isset($USER['bot_enable_expedition']) && (int)$USER['bot_enable_expedition'] !== 1) {
            static::log('EXPO disabled by toggle', [
                'userId'   => $USER['id'] ?? null,
                'planetId' => $PLANET['id'] ?? null,
            ]);
            return false;
        }

        global $resource;

        $USER = static::normalizeUserFactors($USER);

        static::log('EXPO start', [
            'userId'   => $USER['id'],
            'planetId' => $PLANET['id'],
        ]);

        if (empty($resource[124]) || empty($USER[$resource[124]]) || (int)$USER[$resource[124]] < 1) {
            static::log('EXPO no astrophysics / expedition tech', [
                'resource124' => $resource[124] ?? null,
                'value'       => isset($resource[124], $USER[$resource[124]]) ? $USER[$resource[124]] : null,
            ]);
            return false;
        }

        if (!empty($USER['bot_next_expedition']) && $USER['bot_next_expedition'] > TIMESTAMP) {
            static::log('EXPO cooldown active', [
                'next' => $USER['bot_next_expedition'],
                'now'  => TIMESTAMP,
            ]);
            return false;
        }

        require_once ROOT_PATH . 'includes/classes/class.FleetFunctions.php';

        $shipSet = static::getExpeditionShipSet($PLANET);
        $cargoEquivalentNow = static::getCargoEquivalent($shipSet);

        static::log('EXPO ship scan', [
            'planetId'        => $PLANET['id'],
            'ships'           => $shipSet,
            'cargoEquivalent' => $cargoEquivalentNow,
            'needEquivalent'  => static::EXPO_MIN_CARGO_EQUIV,
        ]);

        if ($cargoEquivalentNow < static::EXPO_MIN_CARGO_EQUIV) {
            $needSmallCargo = static::EXPO_MIN_CARGO_EQUIV - $cargoEquivalentNow;

            static::queueShip((int)$PLANET['id'], static::SHIP_SMALL_CARGO, $needSmallCargo);

            static::log('EXPO queued cargos', [
                'planetId' => $PLANET['id'],
                'amount'   => $needSmallCargo,
            ]);
            return false;
        }

        $currentFleets = FleetFunctions::GetCurrentFleets($USER['id']);
        $maxFleets     = FleetFunctions::GetMaxFleetSlots($USER);

        static::log('EXPO fleet slots', [
            'current' => $currentFleets,
            'max'     => $maxFleets,
        ]);

        if ($currentFleets >= $maxFleets) {
            static::log('EXPO no fleet slot free');
            return false;
        }

        $fleetArray = static::buildExpeditionFleet($PLANET);

        static::log('EXPO fleet built', [
            'planetId'   => $PLANET['id'],
            'fleetArray' => $fleetArray,
        ]);

        if (empty($fleetArray)) {
            static::log('EXPO no valid expedition fleet available', [
                'planetId' => $PLANET['id'],
            ]);
            return false;
        }

        $freshPlanet = static::reloadPlanet((int)$PLANET['id']);
        if (!$freshPlanet) {
            static::log('EXPO planet reload failed', [
                'planetId' => $PLANET['id'] ?? null,
            ]);
            return false;
        }

        $PLANET = $freshPlanet;
        $fleetArray = static::sanitizeExpeditionFleetByCurrentPlanet($PLANET, $fleetArray);

        static::log('EXPO fleet after sanitize', [
            'planetId'   => $PLANET['id'],
            'fleetArray' => $fleetArray,
        ]);

        if (empty($fleetArray)) {
            static::log('EXPO abort after sanitize, empty fleet', [
                'planetId' => $PLANET['id'],
            ]);
            return false;
        }

        $cargoEquivalent = static::getFleetCargoEquivalent($fleetArray);
        if ($cargoEquivalent < static::EXPO_MIN_CARGO_EQUIV) {
            static::log('EXPO abort after sanitize, too few cargo ships', [
                'planetId'        => $PLANET['id'],
                'cargoEquivalent' => $cargoEquivalent,
                'needEquivalent'  => static::EXPO_MIN_CARGO_EQUIV,
            ]);
            return false;
        }

        $distance = FleetFunctions::GetTargetDistance(
            [$PLANET['galaxy'], $PLANET['system'], $PLANET['planet']],
            [$PLANET['galaxy'], $PLANET['system'], static::EXPEDITION_PLANET]
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

        $haltSpeed     = Config::get()->halt_speed ?? 1;
        $stayHours     = 1;
        $fleetStayTime = TIMESTAMP + $fleetDuration + (int)($stayHours * 3600 / $haltSpeed);
        $fleetEndTime  = $fleetStayTime + $fleetDuration;

        static::log('EXPO sendFleet', [
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
            static::EXPEDITION_PLANET,
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

        static::log('EXPO sent', [
            'userId'   => $USER['id'],
            'planetId' => $PLANET['id'],
        ]);

        return true;
    }

    protected static function getExpeditionShipSet(array $PLANET): array
    {
        return [
            'SC'  => (int)($PLANET['small_ship_cargo'] ?? 0),
            'LC'  => (int)($PLANET['big_ship_cargo'] ?? 0),
            'SPY' => (int)($PLANET['spy_sonde'] ?? 0),
            'LF'  => (int)($PLANET['light_hunter'] ?? 0),
            'HF'  => (int)($PLANET['heavy_hunter'] ?? 0),
            'CR'  => (int)($PLANET['crusher'] ?? 0),
            'BS'  => (int)($PLANET['battle_ship'] ?? 0),
        ];
    }

    protected static function getCargoEquivalent(array $shipSet): int
    {
        return ((int)$shipSet['SC']) + (((int)$shipSet['LC']) * 2);
    }

    protected static function buildExpeditionFleet(array $PLANET): array
    {
        $ships = static::getExpeditionShipSet($PLANET);
        $fleet = [];

        $usableSC  = (int)$ships['SC'];
        $usableLC  = (int)$ships['LC'];
        $usableSPY = max(0, $ships['SPY'] - static::EXPO_RESERVE_SPY);
        $usableLF  = max(0, $ships['LF']  - static::EXPO_RESERVE_LF);
        $usableHF  = max(0, $ships['HF']  - static::EXPO_RESERVE_HF);
        $usableCR  = max(0, $ships['CR']  - static::EXPO_RESERVE_CR);
        $usableBS  = max(0, $ships['BS']  - static::EXPO_RESERVE_BS);

        if ($usableSC > static::EXPO_MIN_CARGO_EQUIV + static::EXPO_RESERVE_SC) {
            $usableSC -= static::EXPO_RESERVE_SC;
        }

        if ($usableLC > static::EXPO_RESERVE_LC + 1) {
            $usableLC -= static::EXPO_RESERVE_LC;
        }

        $cargoEquivalent = $usableSC + ($usableLC * 2);

        if ($cargoEquivalent < static::EXPO_MIN_CARGO_EQUIV) {
            static::log('EXPO build fleet rejected by usable cargo', [
                'planetId'        => $PLANET['id'] ?? null,
                'usableSC'        => $usableSC,
                'usableLC'        => $usableLC,
                'cargoEquivalent' => $cargoEquivalent,
                'needEquivalent'  => static::EXPO_MIN_CARGO_EQUIV,
            ]);
            return [];
        }

        $sendLC = min($usableLC, 2);
        $remainingNeed = max(0, static::EXPO_MIN_CARGO_EQUIV - ($sendLC * 2));
        $sendSC = min($usableSC, $remainingNeed);

        if ($sendLC === 0 && $sendSC < static::EXPO_MIN_CARGO_EQUIV) {
            $sendSC = min($usableSC, static::EXPO_MIN_CARGO_EQUIV);
        }

        if ($usableLC >= 3) {
            $sendLC = min($usableLC, 3);
        }

        if ($usableSC >= 8) {
            $sendSC = max($sendSC, min($usableSC, 6));
        }

        if ($sendLC > 0) {
            $fleet[static::SHIP_LARGE_CARGO] = $sendLC;
        }

        if ($sendSC > 0) {
            $fleet[static::SHIP_SMALL_CARGO] = $sendSC;
        }

        if ($usableSPY > 0) {
            $fleet[static::SHIP_SPY] = min($usableSPY, 2);
        }

        if ($usableLF > 0) {
            $fleet[static::SHIP_LIGHT_FIGHTER] = min($usableLF, 6);
        }

        if ($usableHF > 0) {
            $fleet[static::SHIP_HEAVY_FIGHTER] = min($usableHF, 3);
        }

        if ($usableCR > 0) {
            $fleet[static::SHIP_CRUISER] = min($usableCR, 2);
        }

        if ($usableBS > 0) {
            $fleet[static::SHIP_BATTLESHIP] = min($usableBS, 1);
        }

        static::log('EXPO build fleet detail', [
            'planetId'  => $PLANET['id'] ?? null,
            'usableSC'  => $usableSC,
            'usableLC'  => $usableLC,
            'usableSPY' => $usableSPY,
            'usableLF'  => $usableLF,
            'usableHF'  => $usableHF,
            'usableCR'  => $usableCR,
            'usableBS'  => $usableBS,
            'sendSC'    => $sendSC,
            'sendLC'    => $sendLC,
            'fleet'     => $fleet,
        ]);

        return array_filter($fleet, static function ($amount) {
            return (int)$amount > 0;
        });
    }

    protected static function sanitizeExpeditionFleetByCurrentPlanet(array $PLANET, array $fleetArray): array
    {
        $available = [
            static::SHIP_SMALL_CARGO   => (int)($PLANET['small_ship_cargo'] ?? 0),
            static::SHIP_LARGE_CARGO   => (int)($PLANET['big_ship_cargo'] ?? 0),
            static::SHIP_SPY           => (int)($PLANET['spy_sonde'] ?? 0),
            static::SHIP_LIGHT_FIGHTER => (int)($PLANET['light_hunter'] ?? 0),
            static::SHIP_HEAVY_FIGHTER => (int)($PLANET['heavy_hunter'] ?? 0),
            static::SHIP_CRUISER       => (int)($PLANET['crusher'] ?? 0),
            static::SHIP_BATTLESHIP    => (int)($PLANET['battle_ship'] ?? 0),
        ];

        foreach ($fleetArray as $shipId => $amount) {
            $amount = (int)$amount;
            $have   = (int)($available[$shipId] ?? 0);

            if ($amount > $have) {
                static::log('EXPO fleet sanitize reduce', [
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

    protected static function getFleetCargoEquivalent(array $fleetArray): int
    {
        $small = (int)($fleetArray[static::SHIP_SMALL_CARGO] ?? 0);
        $large = (int)($fleetArray[static::SHIP_LARGE_CARGO] ?? 0);

        return $small + ($large * 2);
    }
}