<?php
declare(strict_types=1);

class cron_bot_economy
{
    public function run(): void
    {
        /* =========================
         * BOT AI KLASSEN
         * ========================= */
        require_once ROOT_PATH . 'includes/classes/aibot/BotBuildAI.php';
        require_once ROOT_PATH . 'includes/classes/aibot/BotResearchAI.php';
        require_once ROOT_PATH . 'includes/classes/aibot/BotDefenseAI.php';
        require_once ROOT_PATH . 'includes/classes/aibot/BotColonizeAI.php';
        require_once ROOT_PATH . 'includes/classes/aibot/BotColonyBuildAI.php';

        require_once ROOT_PATH . 'includes/vars.php';

        $cache = Cache::get();
        $cache->add('vars', 'VarsBuildCache');
        extract($cache->getData('vars'));

        $db  = Database::get();
        $now = TIMESTAMP;

        $prefix = DB_PREFIX;
        if (substr($prefix, -1) !== '_') {
            $prefix .= '_';
        }

        $usersTable   = $prefix . 'users';
        $planetsTable = $prefix . 'planets';

        /* =========================
         * ALLE BOTS LADEN
         * ========================= */
        $bots = $db->select(
            "SELECT
                id,
                bot_next_action,
                bot_next_colonize
             FROM {$usersTable}
             WHERE is_bot = 1"
        );

        foreach ($bots as $bot) {
            $userId = (int)$bot['id'];

            usleep(random_int(80_000, 200_000));

            /* =========================
             * USER LADEN
             * ========================= */
            $USER = $db->selectSingle(
                "SELECT *
                 FROM {$usersTable}
                 WHERE id = :uid
                   AND is_bot = 1;",
                [
                    ':uid' => $userId,
                ]
            );

            if (!$USER) {
                continue;
            }

            /* =========================
             * BOT ONLINE-ZEIT
             * ========================= */
            if (mt_rand(1, 100) <= 35) {
                $db->update(
                    "UPDATE {$usersTable}
                     SET onlinetime = :time
                     WHERE id = :uid
                       AND is_bot = 1;",
                    [
                        ':time' => $now - random_int(0, 900),
                        ':uid'  => $userId,
                    ]
                );
            }

            /* =========================
             * SPIELERFAKTOREN
             * ========================= */
            $USER['factor'] = getFactors(
                $USER,
                'basic',
                $now
            );

            /* =========================
             * PLANETEN LADEN
             * ========================= */
            $PLANETS = $db->select(
                "SELECT *
                 FROM {$planetsTable}
                 WHERE id_owner = :uid
                   AND planet_type = 1;",
                [
                    ':uid' => $userId,
                ]
            );

            if (empty($PLANETS)) {
                continue;
            }

            /* =========================
             * RESSOURCEN + BAUQUEUE
             * AKTUALISIEREN
             * ========================= */
            foreach ($PLANETS as $PLANET) {
                $PLANET = $this->applyResourceProduction(
                    $USER,
                    $PLANET
                );

                if (
                    (int)$PLANET['b_building'] > 0 &&
                    (int)$PLANET['b_building'] <= $now
                ) {
                    $this->finishPlanetBuilding(
                        $PLANET,
                        $resource,
                        $planetsTable
                    );
                }
            }

            /* =========================
             * HAUPTPLANET AI
             *
             * eigener Cooldown:
             * bot_next_action
             * ========================= */
            $actionReady =
                empty($bot['bot_next_action']) ||
                (int)$bot['bot_next_action'] <= $now;

            if ($actionReady) {
                BotBuildAI::run($userId);
                BotResearchAI::run($userId);
                BotDefenseAI::run($userId);

                $db->update(
                    "UPDATE {$usersTable}
                     SET bot_next_action = :next
                     WHERE id = :uid;",
                    [
                        ':uid'  => $userId,
                        ':next' => $now + random_int(300, 900),
                    ]
                );
            }

            /* =========================
             * KOLONISATION
             *
             * BotColonizeAI besitzt
             * zusätzlich eigenen
             * bot_next_colonize Cooldown
             * ========================= */
            $colonizeReady =
                empty($bot['bot_next_colonize']) ||
                (int)$bot['bot_next_colonize'] <= $now;

            if ($colonizeReady) {
                BotColonizeAI::run($userId);
            }

            /* =========================
             * KOLONIEN AUSBAUEN
             * ========================= */
            BotColonyBuildAI::run($userId);
        }
    }

    /* =========================
     * RESSOURCENPRODUKTION
     * ========================= */
    private function applyResourceProduction(
        array $USER,
        array $PLANET
    ): array {
        require_once ROOT_PATH . 'includes/classes/class.PlanetRessUpdate.php';

        if (empty($USER['factor'])) {
            $USER['factor'] = getFactors(
                $USER,
                'basic',
                TIMESTAMP
            );
        }

        $eco = new ResourceUpdate();
        $eco->CalcResource(
            $USER,
            $PLANET,
            true
        );

        return Database::get()->selectSingle(
            "SELECT *
             FROM " . DB_PREFIX . "planets
             WHERE id = :pid;",
            [
                ':pid' => $PLANET['id'],
            ]
        ) ?: $PLANET;
    }

    /* =========================
     * GEBÄUDE FERTIGSTELLEN
     * ========================= */
    private function finishPlanetBuilding(
        array $PLANET,
        array $resource,
        string $planetsTable
    ): void {
        $db = Database::get();

        $queue = @unserialize(
            $PLANET['b_building_id'] ?? ''
        );

        if (!is_array($queue) || empty($queue[0])) {
            $db->update(
                "UPDATE {$planetsTable}
                 SET
                    b_building = 0,
                    b_building_id = NULL
                 WHERE id = :pid;",
                [
                    ':pid' => $PLANET['id'],
                ]
            );

            return;
        }

        $job   = $queue[0];
        $field = $resource[(int)$job[0]] ?? null;

        if (!$field) {
            return;
        }

        $db->update(
            "UPDATE {$planetsTable}
             SET
                {$field} = :lvl,
                b_building = 0,
                b_building_id = NULL
             WHERE id = :pid;",
            [
                ':lvl' => (int)$job[1],
                ':pid' => $PLANET['id'],
            ]
        );
    }
}