<?php
declare(strict_types=1);

class BotDefenseAI
{
    /** DEFENSE IDs */
    private const ID_ROCKET_LAUNCHER = 401;
    private const ID_LIGHT_LASER     = 402;

    /** MINDESTLEVEL FÜR DEFENSE */
    private const MIN_SHIPYARD_LEVEL = 2;

    public static function run(int $userId): void
    {
        self::log([
            'action' => 'DEFENSE_RUN',
            'uid'    => $userId,
        ]);

        $db = Database::get();

        /* USER LADEN */
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

        /* PLANET LADEN */
        $PLANET = $db->selectSingle(
            "SELECT *
             FROM " . DB_PREFIX . "planets
             WHERE id_owner = :uid
               AND planet_type = 1
             LIMIT 1",
            [':uid' => $userId]
        );

        if (!$PLANET) {
            self::log(['action' => 'NO_PLANET']);
            return;
        }

        /* NICHT BAUEN WENN QUEUE LÄUFT */
        if ((int)$PLANET['b_building'] > time()) {
            self::log([
                'action' => 'SKIP_QUEUE_ACTIVE',
                'until'  => $PLANET['b_building']
            ]);
            return;
        }

        /* WERFTLEVEL */
        if ((int)$PLANET['hangar'] < self::MIN_SHIPYARD_LEVEL) {
            self::log([
                'action' => 'SKIP_SHIPYARD_TOO_LOW',
                'level'  => $PLANET['hangar']
            ]);
            return;
        }

        /* BAUPRIORITÄT */
        $buildOrder = [
            self::ID_ROCKET_LAUNCHER,
            self::ID_LIGHT_LASER
        ];

        foreach ($buildOrder as $defId) {

            if (!self::shouldBuild($USER, $PLANET, $defId)) {
                continue;
            }

            if (self::buildDefense($PLANET, $defId)) {
                return;
            }
        }

        self::log(['action' => 'NO_DEFENSE_ACTION']);
    }

    /* =========================
     * ENTSCHEIDUNGSLOGIK
     * ========================= */
    private static function shouldBuild(array $USER, array $PLANET, int $id): bool
    {
        global $resource;

        // sauberes Spalten-Mapping
        $field = $resource[$id] ?? null;
        $count = (int)($PLANET[$field] ?? 0);

        return match ($id) {

            /* Raketenwerfer max 20 */
            self::ID_ROCKET_LAUNCHER =>
                $count < 20,

            /* Light Laser nur wenn Laser-Tech vorhanden */
            self::ID_LIGHT_LASER =>
                ($USER['laser_tech'] ?? 0) >= 1
                && $count < 10,

            default => false,
        };
    }

    /* =========================
     * BAU STARTEN
     * ========================= */
    private static function buildDefense(array $planet, int $elementId): bool
    {
        global $resource;

        if (!isset($resource[$elementId])) {
            self::log(['action' => 'UNKNOWN_DEF_ID', 'id' => $elementId]);
            return false;
        }

        $field = $resource[$elementId];

        $current = (int)($planet[$field] ?? 0);
        $target  = $current + 1;

        // Baukosten berechnen
        $cost = BuildFunctions::getElementPrice(
            [], // USER optional
            $planet,
            $elementId,
            false,
            $target
        );

        // Ressourcen prüfen
        if (
            $planet['metal']     < ($cost[901] ?? 0) ||
            $planet['crystal']   < ($cost[902] ?? 0) ||
            $planet['deuterium'] < ($cost[903] ?? 0)
        ) {
            self::log([
                'action' => 'NOT_ENOUGH_RES',
                'id'     => $elementId,
                'need'   => $cost
            ]);
            return false;
        }

        /* =========================
         * QUEUE EINTRAGEN (ENGINE FORMAT)
         * ========================= */
        $now = time();
        $end = $now + 12; // Testdauer

        $queue = serialize([
            [$elementId, $target, [], (float)$end, 'build']
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
                ':m'     => $cost[901] ?? 0,
                ':c'     => $cost[902] ?? 0,
                ':d'     => $cost[903] ?? 0,
                ':end'   => $end,
                ':queue' => $queue,
                ':pid'   => $planet['id'],
            ]
        );

        self::log([
            'action' => 'DEFENSE_BUILD_START',
            'id'     => $elementId,
            'from'   => $current,
            'to'     => $target,
        ]);

        return true;
    }

    /* =========================
     * DEBUG LOG
     * ========================= */
    private static function log(array $data): void
    {
        $dir = ROOT_PATH . 'includes/ai_log/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data['time']     = time();
        $data['datetime'] = date('Y-m-d H:i:s');

        file_put_contents(
            $dir . 'bot_defense.json',
            json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );
    }
}
