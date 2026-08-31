<?php
declare(strict_types=1);

class BotAIResourceTransfer
{
    private const SHIP_SMALL_CARGO = 202;
    private const SHIP_BIG_CARGO   = 203;

    private const SMALL_CARGO_CAPACITY = 5000;
    private const BIG_CARGO_CAPACITY   = 25000;

    private const MISSION_TRANSPORT = 3;

    /*
     * Ein Flottenslot bleibt immer frei.
     *
     * Beispiel:
     * maxSlots = 6
     * usedSlots = 4
     * freeSlots = 2
     *
     * Transport darf starten.
     * Danach bleibt noch 1 Slot frei.
     */
    private const FLEET_SLOT_RESERVE = 1;

    /*
     * Reserve, die auf einem Quellplaneten liegen bleibt.
     */
    private const RESERVE_METAL     = 300000;
    private const RESERVE_CRYSTAL   = 200000;
    private const RESERVE_DEUTERIUM = 200000;

    /*
     * Nicht die komplette theoretische Ladekapazität benutzen.
     * Etwas Reserve für Rundung/Treibstoff.
     */
    private const CAPACITY_FACTOR = 0.95;

    /**
     * Versucht fehlende Rohstoffe für einen Bauauftrag
     * von einem anderen eigenen Planeten zu holen.
     *
     * $needMetal / Crystal / Deuterium sind bereits die
     * tatsächlich FEHLENDEN Mengen.
     */
    public static function requestForBuild(
        int $userId,
        array $targetPlanet,
        float $needMetal,
        float $needCrystal,
        float $needDeuterium
    ): bool {
        $needMetal     = max(0, $needMetal);
        $needCrystal   = max(0, $needCrystal);
        $needDeuterium = max(0, $needDeuterium);

        if (
            $needMetal <= 0 &&
            $needCrystal <= 0 &&
            $needDeuterium <= 0
        ) {
            return false;
        }

        $db = Database::get();

        $USER = $db->selectSingle(
            "SELECT *
             FROM " . DB_PREFIX . "users
             WHERE id = :uid
               AND is_bot = 1",
            [
                ':uid' => $userId,
            ]
        );

        if (!$USER) {
            return false;
        }

        /*
         * FleetFunctions brauchen wir bereits hier,
         * weil wir die maximalen Flottenslots prüfen.
         */
        require_once ROOT_PATH . 'includes/classes/class.FleetFunctions.php';

        /* =========================
         * FLOTTENSLOTS PRÜFEN
         * ========================= */
        $slotState = self::getFleetSlotState($USER);

        /*
         * Ein Transport benötigt selbst einen Slot.
         * Zusätzlich soll FLEET_SLOT_RESERVE frei bleiben.
         *
         * Bei Reserve = 1 müssen also mindestens
         * 2 Slots frei sein.
         */
        if (
            $slotState['freeSlots']
            <= self::FLEET_SLOT_RESERVE
        ) {
            self::log([
                'action'       => 'TRANSFER_SKIP_NO_FLEET_SLOT',
                'userId'       => $userId,
                'targetId'     => $targetPlanet['id'] ?? 0,
                'maxSlots'     => $slotState['maxSlots'],
                'usedSlots'    => $slotState['usedSlots'],
                'freeSlots'    => $slotState['freeSlots'],
                'slotReserve'  => self::FLEET_SLOT_RESERVE,
            ]);

            return false;
        }

        /*
         * V1: Pro Bot nur ein aktiver Ressourcentransport.
         * So verhindern wir zunächst Flotten-Spam.
         */
        if (self::hasActiveResourceTransport($userId)) {
            self::log([
                'action'   => 'TRANSFER_SKIP_ACTIVE',
                'userId'   => $userId,
                'targetId' => $targetPlanet['id'] ?? 0,
            ]);

            return false;
        }

        /*
         * Alle anderen eigenen Planeten suchen.
         * Planeten mit viel Rohstoffüberschuss zuerst.
         */
        $sources = $db->select(
            "SELECT *
             FROM " . DB_PREFIX . "planets
             WHERE id_owner = :uid
               AND planet_type = 1
               AND id != :target
             ORDER BY
                (metal + crystal + deuterium) DESC",
            [
                ':uid'    => $userId,
                ':target' => (int)$targetPlanet['id'],
            ]
        );

        if (empty($sources)) {
            return false;
        }

        foreach ($sources as $sourcePlanet) {
            if (self::trySendFromPlanet(
                $USER,
                $sourcePlanet,
                $targetPlanet,
                $needMetal,
                $needCrystal,
                $needDeuterium
            )) {
                return true;
            }
        }

        self::log([
            'action'          => 'TRANSFER_NO_SOURCE',
            'userId'          => $userId,
            'targetId'        => $targetPlanet['id'] ?? 0,
            'needMetal'       => (int)$needMetal,
            'needCrystal'     => (int)$needCrystal,
            'needDeuterium'   => (int)$needDeuterium,
        ]);

        return false;
    }

    private static function trySendFromPlanet(
        array $USER,
        array $source,
        array $target,
        float $needMetal,
        float $needCrystal,
        float $needDeuterium
    ): bool {
        require_once ROOT_PATH . 'includes/vars.php';
        require_once ROOT_PATH . 'includes/classes/class.FleetFunctions.php';

        /*
         * Noch einmal direkt vor dem Versand prüfen.
         *
         * Falls zwischen requestForBuild() und hier eine andere
         * Bot-Aktion eine Flotte gestartet hat, schützen wir uns
         * vor einem belegten letzten Slot.
         */
        $slotState = self::getFleetSlotState($USER);

        if (
            $slotState['freeSlots']
            <= self::FLEET_SLOT_RESERVE
        ) {
            self::log([
                'action'       => 'TRANSFER_SKIP_NO_FLEET_SLOT',
                'userId'       => $USER['id'],
                'sourceId'     => $source['id'] ?? 0,
                'targetId'     => $target['id'] ?? 0,
                'maxSlots'     => $slotState['maxSlots'],
                'usedSlots'    => $slotState['usedSlots'],
                'freeSlots'    => $slotState['freeSlots'],
                'slotReserve'  => self::FLEET_SLOT_RESERVE,
            ]);

            return false;
        }

        /*
         * Nur Überschuss darf den Quellplaneten verlassen.
         */
        $availableMetal = max(
            0,
            (float)($source['metal'] ?? 0) - self::RESERVE_METAL
        );

        $availableCrystal = max(
            0,
            (float)($source['crystal'] ?? 0) - self::RESERVE_CRYSTAL
        );

        $availableDeuterium = max(
            0,
            (float)($source['deuterium'] ?? 0) - self::RESERVE_DEUTERIUM
        );

        $wantedMetal = min(
            $needMetal,
            $availableMetal
        );

        $wantedCrystal = min(
            $needCrystal,
            $availableCrystal
        );

        $wantedDeuterium = min(
            $needDeuterium,
            $availableDeuterium
        );

        $wantedTotal =
            $wantedMetal +
            $wantedCrystal +
            $wantedDeuterium;

        if ($wantedTotal <= 0) {
            return false;
        }

        /*
         * Vorhandene Transporter.
         */
        $bigAvailable = max(
            0,
            (int)($source['big_ship_cargo'] ?? 0)
        );

        $smallAvailable = max(
            0,
            (int)($source['small_ship_cargo'] ?? 0)
        );

        if (
            $bigAvailable <= 0 &&
            $smallAvailable <= 0
        ) {
            self::log([
                'action'   => 'TRANSFER_NO_CARGO_SHIPS',
                'userId'   => $USER['id'],
                'sourceId' => $source['id'],
                'targetId' => $target['id'],
            ]);

            return false;
        }

        /*
         * Erst große Transporter verwenden.
         */
        $fleet = [];

        $remainingCapacityNeed = $wantedTotal;

        if ($bigAvailable > 0) {
            $neededBig = (int)ceil(
                $remainingCapacityNeed /
                (
                    self::BIG_CARGO_CAPACITY
                    * self::CAPACITY_FACTOR
                )
            );

            $useBig = min(
                $bigAvailable,
                $neededBig
            );

            if ($useBig > 0) {
                $fleet[self::SHIP_BIG_CARGO] = $useBig;

                $remainingCapacityNeed -=
                    $useBig
                    * self::BIG_CARGO_CAPACITY
                    * self::CAPACITY_FACTOR;
            }
        }

        /*
         * Wenn nötig mit kleinen Transportern auffüllen.
         */
        if (
            $remainingCapacityNeed > 0 &&
            $smallAvailable > 0
        ) {
            $neededSmall = (int)ceil(
                $remainingCapacityNeed /
                (
                    self::SMALL_CARGO_CAPACITY
                    * self::CAPACITY_FACTOR
                )
            );

            $useSmall = min(
                $smallAvailable,
                $neededSmall
            );

            if ($useSmall > 0) {
                $fleet[self::SHIP_SMALL_CARGO] =
                    $useSmall;
            }
        }

        if (empty($fleet)) {
            return false;
        }

        /*
         * Tatsächlich nutzbare Ladekapazität.
         */
        $capacity = 0.0;

        if (!empty($fleet[self::SHIP_BIG_CARGO])) {
            $capacity +=
                $fleet[self::SHIP_BIG_CARGO]
                * self::BIG_CARGO_CAPACITY;
        }

        if (!empty($fleet[self::SHIP_SMALL_CARGO])) {
            $capacity +=
                $fleet[self::SHIP_SMALL_CARGO]
                * self::SMALL_CARGO_CAPACITY;
        }

        $capacity *= self::CAPACITY_FACTOR;

        if ($capacity <= 0) {
            return false;
        }

        /*
         * Falls nicht alles hineinpasst:
         * Metall/Kristall/Deuterium proportional reduzieren.
         */
        $scale = min(
            1.0,
            $capacity / $wantedTotal
        );

        $sendMetal =
            (int)floor($wantedMetal * $scale);

        $sendCrystal =
            (int)floor($wantedCrystal * $scale);

        $sendDeuterium =
            (int)floor($wantedDeuterium * $scale);

        if (
            $sendMetal <= 0 &&
            $sendCrystal <= 0 &&
            $sendDeuterium <= 0
        ) {
            return false;
        }

        if (
            !isset($USER['factor']) &&
            function_exists('getFactors')
        ) {
            $USER['factor'] = getFactors(
                $USER,
                'basic',
                TIMESTAMP
            );
        }

        /*
         * Flugzeit wie bei BotColonizeAI.
         */
        $distance = FleetFunctions::GetTargetDistance(
            [
                (int)$source['galaxy'],
                (int)$source['system'],
                (int)$source['planet'],
            ],
            [
                (int)$target['galaxy'],
                (int)$target['system'],
                (int)$target['planet'],
            ]
        );

        $speed = FleetFunctions::GetFleetMaxSpeed(
            $fleet,
            $USER
        );

        $duration =
            (int)FleetFunctions::GetMissionDuration(
                10,
                $speed,
                $distance,
                FleetFunctions::GetGameSpeedFactor(),
                $USER
            );

        $arrival =
            TIMESTAMP + $duration;

        $return =
            $arrival + $duration;

        $consumption =
            (int)FleetFunctions::GetFleetConsumption(
                $fleet,
                $duration,
                $distance,
                $USER,
                FleetFunctions::GetGameSpeedFactor()
            );

        /*
         * Zusätzliche Sicherheit:
         * Treibstoff muss auf dem Quellplaneten vorhanden sein.
         */
        $sourceDeuterium =
            (float)($source['deuterium'] ?? 0);

        if (
            $sourceDeuterium <
            (
                $sendDeuterium
                + $consumption
                + self::RESERVE_DEUTERIUM
            )
        ) {
            $maxSendDeuterium = max(
                0,
                (int)floor(
                    $sourceDeuterium
                    - $consumption
                    - self::RESERVE_DEUTERIUM
                )
            );

            $sendDeuterium = min(
                $sendDeuterium,
                $maxSendDeuterium
            );
        }

        /*
         * Letzte Slot-Prüfung unmittelbar vor sendFleet().
         */
        $slotState = self::getFleetSlotState($USER);

        if (
            $slotState['freeSlots']
            <= self::FLEET_SLOT_RESERVE
        ) {
            self::log([
                'action'       => 'TRANSFER_SKIP_NO_FLEET_SLOT',
                'userId'       => $USER['id'],
                'sourceId'     => $source['id'],
                'targetId'     => $target['id'],
                'maxSlots'     => $slotState['maxSlots'],
                'usedSlots'    => $slotState['usedSlots'],
                'freeSlots'    => $slotState['freeSlots'],
                'slotReserve'  => self::FLEET_SLOT_RESERVE,
            ]);

            return false;
        }

        /*
         * Transportmission an eigenen Planeten.
         */
        FleetFunctions::sendFleet(
            $fleet,
            self::MISSION_TRANSPORT,

            (int)$USER['id'],
            (int)$source['id'],
            (int)$source['galaxy'],
            (int)$source['system'],
            (int)$source['planet'],
            1,

            (int)$USER['id'],
            (int)$target['id'],
            (int)$target['galaxy'],
            (int)$target['system'],
            (int)$target['planet'],
            1,

            [
                901 => $sendMetal,
                902 => $sendCrystal,
                903 => $sendDeuterium,
            ],

            $arrival,
            $arrival,
            $return,

            0,
            0,
            0,
            $consumption
        );

        /*
         * Slot-Zustand nach dem Start nur fürs Log.
         */
        $slotStateAfter =
            self::getFleetSlotState($USER);

        self::log([
            'action'          => 'TRANSFER_SENT',
            'userId'          => $USER['id'],
            'sourceId'        => $source['id'],
            'targetId'        => $target['id'],
            'metal'           => $sendMetal,
            'crystal'         => $sendCrystal,
            'deuterium'       => $sendDeuterium,
            'bigCargo'        =>
                $fleet[self::SHIP_BIG_CARGO] ?? 0,
            'smallCargo'      =>
                $fleet[self::SHIP_SMALL_CARGO] ?? 0,
            'capacity'        => (int)$capacity,
            'consumption'     => $consumption,
            'arrival'         => $arrival,

            /*
             * Zum Testen sehr hilfreich.
             */
            'maxSlots'        =>
                $slotStateAfter['maxSlots'],
            'usedSlotsAfter'  =>
                $slotStateAfter['usedSlots'],
            'freeSlotsAfter'  =>
                $slotStateAfter['freeSlots'],
        ]);

        return true;
    }

    /* =========================
     * FLOTTENSLOTS
     * ========================= */
   private static function getFleetSlotState(array $USER): array
{
    require_once ROOT_PATH . 'includes/vars.php';
    require_once ROOT_PATH . 'includes/classes/class.FleetFunctions.php';

    /*
     * FleetFunctions::GetMaxFleetSlots() benötigt
     * den factor-Eintrag im USER-Array.
     */
    if (!isset($USER['factor']) && function_exists('getFactors')) {
        $USER['factor'] = getFactors(
            $USER,
            'basic',
            TIMESTAMP
        );
    }

    /*
     * Maximale Flottenslots entsprechend
     * der normalen Spielmechanik.
     */
    $maxSlots = (int)FleetFunctions::GetMaxFleetSlots($USER);

    $universe = (int)($USER['universe'] ?? 1);

    /*
     * Alle momentan vorhandenen Flotten dieses Bots zählen.
     * Dazu gehören Angriff, Expedition, Spionage,
     * Kolonisation, Transport und Rückflüge.
     */
    $row = Database::get()->selectSingle(
        "SELECT COUNT(*) AS cnt
         FROM " . DB_PREFIX . "fleets
         WHERE fleet_owner = :uid
           AND fleet_universe = :universe",
        [
            ':uid'      => (int)$USER['id'],
            ':universe' => $universe,
        ]
    );

    $usedSlots = (int)($row['cnt'] ?? 0);

    $freeSlots = max(
        0,
        $maxSlots - $usedSlots
    );

    return [
        'maxSlots'  => $maxSlots,
        'usedSlots' => $usedSlots,
        'freeSlots' => $freeSlots,
    ];
}

    /**
     * V1:
     * Solange irgendeine eigene Transportmission läuft,
     * startet die Logistik keine zweite.
     */
    private static function hasActiveResourceTransport(
        int $userId
    ): bool {
        $row = Database::get()->selectSingle(
            "SELECT fleet_id
             FROM " . DB_PREFIX . "fleets
             WHERE fleet_owner = :uid
               AND fleet_mission = :mission
             LIMIT 1",
            [
                ':uid' =>
                    $userId,

                ':mission' =>
                    self::MISSION_TRANSPORT,
            ]
        );

        return !empty($row);
    }

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
            $dir . 'bot_resource_transfer.json',
            json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
            ) . PHP_EOL,
            FILE_APPEND
        );
    }
}