<?php

class ShowCollectMinesPage extends AbstractGamePage
{
    public static $requireModule = MODULE_COLLECT_MINES;

    function __construct()
    {
        parent::__construct();
    }

    function show()
    {
        global $USER, $PLANET, $resource, $LNG, $db, $config;

        if (isVacationMode($USER)) {
            $this->printMessage($LNG['cm_error_1'], true);
            return;
        }

        $from = preg_replace('/[^a-zA-Z0-9_]/', '', HTTP::_GP('from', ''));

        // Under attack check
        if (!$config->collect_mines_under_attack) {

            $sql = "SELECT COUNT(*) AS count
                    FROM %%FLEETS%%
                    WHERE fleet_owner != :userId
                      AND fleet_target_owner = :userId
                      AND fleet_mess = 0
                      AND fleet_mission = 1
                      AND fleet_start_time < :limitTime";

            $attackingFleetsCount = $db->selectSingle($sql, [
                ':userId'     => $USER['id'],
                ':limitTime' => TIMESTAMP + 5 * 60
            ], 'count');

            if ($attackingFleetsCount > 0) {
                $this->printMessage($LNG['cm_error_2'], true);
                return;
            }
        }

        // Cooldown check
        $timelimit   = (int)$config->collect_mine_time_minutes * 60;
        $lastcollect = TIMESTAMP - (int)$USER['last_collect_mine_time'];

        if ($lastcollect < $timelimit) {
            $this->printMessage(
                sprintf($LNG['cm_error_3'], $config->collect_mine_time_minutes),
                true
            );
            return;
        }

        /* =========================
           DARK MATTER CHECK (TEST)
           ========================= */

        $dmCost = 50;

        if ($USER['darkmatter'] < $dmCost) {
            $this->printMessage('Nicht genug Dunkle Materie!', true);
            return;
        }

        // DM abziehen
        $USER['darkmatter'] -= $dmCost;

        $sql = "UPDATE %%USERS%%
                SET darkmatter = darkmatter - :dm
                WHERE id = :userId";

        $db->update($sql, [
            ':dm'     => $dmCost,
            ':userId'=> $USER['id']
        ]);

        /* =========================
           RESOURCE COLLECTION
           ========================= */

        $PlanetRess = new ResourceUpdate();

        $sql = "SELECT *
                FROM %%PLANETS%%
                WHERE id_owner = :userId
                  AND destruyed = 0";

        $PlanetsRAW = $db->select($sql, [
            ':userId' => $USER['id']
        ]);

        $PLANETS = [];

        foreach ($PlanetsRAW as $CPLANET) {
            list($USER, $CPLANET) = $PlanetRess->CalcResource($USER, $CPLANET, true);
            $PLANETS[] = $CPLANET;
        }

        $metalSum = $crystalSum = $deuteriumSum = 0;

        foreach ($PLANETS as $currentPlanet) {
            if ($currentPlanet['id'] != $PLANET['id']) {
                $metalSum     += (int)$currentPlanet['metal'];
                $crystalSum   += (int)$currentPlanet['crystal'];
                $deuteriumSum += (int)$currentPlanet['deuterium'];
            }
        }

        // Reset other planets
        $sql = "UPDATE %%PLANETS%%
                SET metal = 0, crystal = 0, deuterium = 0
                WHERE id_owner = :userId
                  AND id != :planetId";

        $db->update($sql, [
            ':userId'    => $USER['id'],
            ':planetId' => $PLANET['id']
        ]);

        // Add resources to current planet
        $PLANET[$resource[901]] += $metalSum;
        $PLANET[$resource[902]] += $crystalSum;
        $PLANET[$resource[903]] += $deuteriumSum;

        // Save current planet
        $sql = "UPDATE %%PLANETS%%
                SET metal = :metal,
                    crystal = :crystal,
                    deuterium = :deuterium
                WHERE id = :planetId";

        $db->update($sql, [
            ':metal'     => $PLANET[$resource[901]],
            ':crystal'   => $PLANET[$resource[902]],
            ':deuterium' => $PLANET[$resource[903]],
            ':planetId'  => $PLANET['id']
        ]);

        // Update collect time
        $sql = "UPDATE %%USERS%%
                SET last_collect_mine_time = :time
                WHERE id = :userId";

        $db->update($sql, [
            ':time'   => TIMESTAMP,
            ':userId'=> $USER['id']
        ]);

        $this->redirectTo("game.php?page={$from}");
    }
}
