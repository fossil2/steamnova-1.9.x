<?php
declare(strict_types=1);

require_once ROOT_PATH . 'includes/classes/aibot/BotAICommon.php';
require_once ROOT_PATH . 'includes/classes/aibot/BotAISpy.php';
require_once ROOT_PATH . 'includes/classes/aibot/BotAIExpedition.php';
require_once ROOT_PATH . 'includes/classes/aibot/BotAIAttack.php';

class BotAIManager extends BotAICommon
{
    public static function run(int $botId): void
    {
        $USER = static::reloadBotUser($botId);

        static::log('RUN start', [
            'botId' => $botId,
        ]);

        if (!$USER) {
            static::log('RUN no bot user found', [
                'botId' => $botId,
            ]);
            return;
        }

        /*
         * Merkt sich alle Planeten, von denen in diesem
         * Bot-Durchlauf bereits eine Flotte gestartet wurde.
         *
         * Dadurch kann derselbe Planet nicht innerhalb
         * desselben Runs gleichzeitig für Spy, Expedition
         * und Angriff verwendet werden.
         */
        $fleetActionPlanets = [];

        /*
         * -------------------------------------------------
         * PLANETEN FÜR SPY / EXPEDITION LADEN
         * -------------------------------------------------
         */
        $PLANETS = Database::get()->select(
            'SELECT *
             FROM %%PLANETS%%
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY galaxy ASC, system ASC, planet ASC;',
            [
                ':uid' => $botId,
            ]
        );

        if (empty($PLANETS) || !is_array($PLANETS)) {
            static::log('RUN no planets found', [
                'botId' => $botId,
            ]);
            return;
        }

        static::log('RUN planets loaded', [
            'userId'      => $USER['id'],
            'planetCount' => count($PLANETS),
        ]);

        /*
         * -------------------------------------------------
         * SPIONAGE / EXPEDITION
         * -------------------------------------------------
         *
         * Pro Planet maximal EINE erfolgreiche Flottenaktion.
         *
         * Priorität:
         * 1. Spy
         * 2. Expedition
         */
        foreach ($PLANETS as $PLANET) {
            $planetId = (int)$PLANET['id'];

            static::log('RUN process planet', [
                'userId'   => $USER['id'],
                'planetId' => $planetId,
                'galaxy'   => $PLANET['galaxy'] ?? null,
                'system'   => $PLANET['system'] ?? null,
                'planet'   => $PLANET['planet'] ?? null,
            ]);

            /*
             * ---------------------------------------------
             * SPIONAGE
             * ---------------------------------------------
             */
            $spyStarted = BotAISpy::runPlanet(
                $USER,
                $PLANET
            );

            if ($spyStarted === true) {
                $fleetActionPlanets[$planetId] = true;

                static::log('RUN planet fleet action done', [
                    'userId'   => $USER['id'],
                    'planetId' => $planetId,
                    'action'   => 'spy',
                ]);

                /*
                 * USER neu laden, da die Spy-Aktion
                 * Cooldowns/Felder geändert haben kann.
                 */
                $freshUser = static::reloadBotUser($botId);

                if ($freshUser !== null) {
                    $USER = $freshUser;
                }

                /*
                 * Auf diesem Planeten in diesem Tick
                 * keine Expedition mehr starten.
                 */
                continue;
            }

            /*
             * ---------------------------------------------
             * PLANET VOR EXPEDITION NEU LADEN
             * ---------------------------------------------
             *
             * Wichtig:
             * Spy oder andere Aktionen könnten den Bestand
             * inzwischen geändert haben.
             */
            $freshPlanet = static::reloadPlanet($planetId);

            if (!$freshPlanet) {
                static::log('RUN planet reload failed', [
                    'userId'   => $USER['id'],
                    'planetId' => $planetId,
                    'before'   => 'expedition',
                ]);
                continue;
            }

            /*
             * ---------------------------------------------
             * EXPEDITION
             * ---------------------------------------------
             */
            $expeditionStarted = BotAIExpedition::runPlanet(
                $USER,
                $freshPlanet
            );

            if ($expeditionStarted === true) {
                $fleetActionPlanets[$planetId] = true;

                static::log('RUN planet fleet action done', [
                    'userId'   => $USER['id'],
                    'planetId' => $planetId,
                    'action'   => 'expedition',
                ]);

                /*
                 * USER neu laden, da bot_next_expedition
                 * geändert wurde.
                 */
                $freshUser = static::reloadBotUser($botId);

                if ($freshUser !== null) {
                    $USER = $freshUser;
                }

                continue;
            }
        }

        /*
         * -------------------------------------------------
         * PLANETEN FÜR ANGRIFF KOMPLETT NEU LADEN
         * -------------------------------------------------
         *
         * Nicht das alte $PLANETS-Array verwenden.
         *
         * Spy und Expedition können inzwischen Schiffe
         * vom Planeten entfernt haben.
         */
        $ATTACK_PLANETS = Database::get()->select(
            'SELECT *
             FROM %%PLANETS%%
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY galaxy ASC, system ASC, planet ASC;',
            [
                ':uid' => $botId,
            ]
        );

        if (
            empty($ATTACK_PLANETS) ||
            !is_array($ATTACK_PLANETS)
        ) {
            static::log('RUN no attack planets found', [
                'userId' => $USER['id'],
            ]);

            static::log('RUN finished', [
                'userId'      => $USER['id'],
                'planetCount' => count($PLANETS),
            ]);

            return;
        }

        /*
         * Planeten mit den meisten Kampfschiffen zuerst.
         *
         * Jetzt auf Basis des aktuellen Datenbankstands.
         */
        usort(
            $ATTACK_PLANETS,
            static function (array $a, array $b): int {
                $combatA = BotAIAttack::getPlanetCombatShips($a);
                $combatB = BotAIAttack::getPlanetCombatShips($b);

                return $combatB <=> $combatA;
            }
        );

        /*
         * -------------------------------------------------
         * ANGRIFF
         * -------------------------------------------------
         */
        foreach ($ATTACK_PLANETS as $PLANET) {
            $planetId = (int)$PLANET['id'];

            /*
             * Auf diesem Planeten wurde in diesem Run
             * bereits Spy oder Expedition gestartet.
             *
             * Also keinen Angriff mehr von hier starten.
             */
            if (isset($fleetActionPlanets[$planetId])) {
                static::log('RUN attack skip planet already used', [
                    'userId'   => $USER['id'],
                    'planetId' => $planetId,
                ]);
                continue;
            }

            /*
             * Direkt vor dem Angriff nochmals frisch laden.
             */
            $freshPlanet = static::reloadPlanet($planetId);

            if (!$freshPlanet) {
                static::log('RUN planet reload failed', [
                    'userId'   => $USER['id'],
                    'planetId' => $planetId,
                    'before'   => 'attack',
                ]);
                continue;
            }

            /*
             * USER ebenfalls frisch halten.
             */
            $freshUser = static::reloadBotUser($botId);

            if ($freshUser !== null) {
                $USER = $freshUser;
            }

            /*
             * Falls inzwischen bereits ein Angriff gestartet
             * wurde und der Cooldown gesetzt ist, abbrechen.
             */
            if (
                !empty($USER['bot_next_attack']) &&
                (int)$USER['bot_next_attack'] > TIMESTAMP
            ) {
                static::log('RUN attack cooldown active', [
                    'userId' => $USER['id'],
                    'next'   => $USER['bot_next_attack'],
                    'now'    => TIMESTAMP,
                ]);
                break;
            }

            /*
             * Angriff starten.
             */
            $attackStarted = BotAIAttack::runPlanet(
                $USER,
                $freshPlanet
            );

            if ($attackStarted === true) {
                $fleetActionPlanets[$planetId] = true;

                static::log('RUN planet fleet action done', [
                    'userId'   => $USER['id'],
                    'planetId' => $planetId,
                    'action'   => 'attack',
                ]);
            }

            /*
             * USER nach dem Angriff erneut laden,
             * damit bot_next_attack aktuell ist.
             */
            $freshUser = static::reloadBotUser($botId);

            if ($freshUser !== null) {
                $USER = $freshUser;
            }

            /*
             * Ein erfolgreicher Angriff setzt normalerweise
             * bot_next_attack. Danach keine weiteren
             * Angriffe in diesem Run starten.
             */
            if (
                !empty($USER['bot_next_attack']) &&
                (int)$USER['bot_next_attack'] > TIMESTAMP
            ) {
                break;
            }
        }

        static::log('RUN finished', [
            'userId'              => $USER['id'],
            'planetCount'         => count($PLANETS),
            'fleetActionPlanets'  => array_keys($fleetActionPlanets),
        ]);
    }

    /*
     * =====================================================
     * NUR SPIONAGE
     * =====================================================
     */
    public static function runSpyOnly(int $botId): void
    {
        $USER = static::reloadBotUser($botId);

        if (!$USER) {
            return;
        }

        $PLANETS = Database::get()->select(
            'SELECT *
             FROM %%PLANETS%%
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY galaxy ASC, system ASC, planet ASC;',
            [
                ':uid' => $botId,
            ]
        );

        if (empty($PLANETS) || !is_array($PLANETS)) {
            return;
        }

        foreach ($PLANETS as $PLANET) {
            $planetId = (int)$PLANET['id'];

            $freshPlanet = static::reloadPlanet($planetId);

            if (!$freshPlanet) {
                continue;
            }

            BotAISpy::runPlanet(
                $USER,
                $freshPlanet
            );

            $freshUser = static::reloadBotUser($botId);

            if ($freshUser !== null) {
                $USER = $freshUser;
            }
        }
    }

    /*
     * =====================================================
     * NUR EXPEDITION
     * =====================================================
     */
    public static function runExpeditionOnly(int $botId): void
    {
        $USER = static::reloadBotUser($botId);

        if (!$USER) {
            return;
        }

        $PLANETS = Database::get()->select(
            'SELECT *
             FROM %%PLANETS%%
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY galaxy ASC, system ASC, planet ASC;',
            [
                ':uid' => $botId,
            ]
        );

        if (empty($PLANETS) || !is_array($PLANETS)) {
            return;
        }

        foreach ($PLANETS as $PLANET) {
            $planetId = (int)$PLANET['id'];

            $freshPlanet = static::reloadPlanet($planetId);

            if (!$freshPlanet) {
                continue;
            }

            $started = BotAIExpedition::runPlanet(
                $USER,
                $freshPlanet
            );

            if ($started === true) {
                $freshUser = static::reloadBotUser($botId);

                if ($freshUser !== null) {
                    $USER = $freshUser;
                }
            }
        }
    }

    /*
     * =====================================================
     * NUR ANGRIFF
     * =====================================================
     */
    public static function runAttackOnly(int $botId): void
    {
        $USER = static::reloadBotUser($botId);

        if (!$USER) {
            return;
        }

        $PLANETS = Database::get()->select(
            'SELECT *
             FROM %%PLANETS%%
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY galaxy ASC, system ASC, planet ASC;',
            [
                ':uid' => $botId,
            ]
        );

        if (empty($PLANETS) || !is_array($PLANETS)) {
            return;
        }

        usort(
            $PLANETS,
            static function (array $a, array $b): int {
                $combatA = BotAIAttack::getPlanetCombatShips($a);
                $combatB = BotAIAttack::getPlanetCombatShips($b);

                return $combatB <=> $combatA;
            }
        );

        foreach ($PLANETS as $PLANET) {
            /*
             * USER vor jedem Angriffsversuch frisch laden.
             */
            $freshUser = static::reloadBotUser($botId);

            if ($freshUser !== null) {
                $USER = $freshUser;
            }

            if (
                !empty($USER['bot_next_attack']) &&
                (int)$USER['bot_next_attack'] > TIMESTAMP
            ) {
                break;
            }

            /*
             * Planet unmittelbar vor dem Angriff
             * aus der DB aktualisieren.
             */
            $freshPlanet = static::reloadPlanet(
                (int)$PLANET['id']
            );

            if (!$freshPlanet) {
                continue;
            }

            BotAIAttack::runPlanet(
                $USER,
                $freshPlanet
            );

            $freshUser = static::reloadBotUser($botId);

            if ($freshUser !== null) {
                $USER = $freshUser;
            }

            if (
                !empty($USER['bot_next_attack']) &&
                (int)$USER['bot_next_attack'] > TIMESTAMP
            ) {
                break;
            }
        }
    }
}