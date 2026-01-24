<?php

/**
 *  2Moons
 *   by Jan-Otto Kröpke 2009-2016
 *
 * For the full copyright and license information, please view the LICENSE
 *
 * @package 2Moons
 * @author Jan-Otto Kröpke <slaver7@gmail.com>
 * @copyright 2009 Lucky
 * @copyright 2016 Jan-Otto Kröpke <slaver7@gmail.com>
 * @licence MIT
 * @version 1.8.x Koray Karakuş <koraykarakus@yahoo.com>
 * @link https://github.com/jkroepke/2Moons
 */

class ShowFleetMissilePage extends AbstractGamePage
{
    public static $requireModule = MODULE_MISSILEATTACK;

    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        global $USER, $PLANET, $LNG, $reslist, $resource;

        $targetGalaxy  = HTTP::_GP('galaxy', 0);
        $targetSystem  = HTTP::_GP('system', 0);
        $targetPlanet  = HTTP::_GP('planet', 0);
        $targetType    = HTTP::_GP('type', 0);
        $anz           = min(HTTP::_GP('SendMI', 0), (int)$PLANET['interplanetary_misil']);
        $primaryTarget = HTTP::_GP('Target', 0);

        $db = Database::get();

        /* =========================
         * TARGET PLANET (SAFE)
         * ========================= */
        $target = $db->selectSingle(
            'SELECT id, id_owner FROM %%PLANETS%%
             WHERE universe = :universe
               AND galaxy = :g
               AND system = :s
               AND planet = :p
               AND planet_type = :t;',
            [
                ':universe' => Universe::current(),
                ':g'        => $targetGalaxy,
                ':s'        => $targetSystem,
                ':p'        => $targetPlanet,
                ':t'        => $targetType,
            ]
        );

        if ($target === false) {
            $target = [
                'id'       => 0,
                'id_owner' => 0,
            ];
        }

        /* =========================
         * RANGE + BASIC CHECKS
         * ========================= */
        $Range     = FleetFunctions::GetMissileRange($USER[$resource[117]]);
        $systemMin = $PLANET['system'] - $Range;
        $systemMax = $PLANET['system'] + $Range;

        $error = [];

        if (IsVacationMode($USER)) {
            $error[] = $LNG['fl_vacation_mode_active'];
        }

        if ($PLANET['silo'] < 4) {
            $error[] = $LNG['ma_silo_level'];
        }

        if ($USER['impulse_motor_tech'] == 0) {
            $error[] = $LNG['ma_impulse_drive_required'];
        }

        if ($targetGalaxy != $PLANET['galaxy'] || $targetSystem < $systemMin || $targetSystem > $systemMax) {
            $error[] = $LNG['ma_not_send_other_galaxy'];
        }

        if ($target['id'] === 0) {
            $error[] = $LNG['ma_planet_doesnt_exists'];
        }

        if (!in_array($primaryTarget, $reslist['defense']) && $primaryTarget != 0) {
            $error[] = $LNG['ma_wrong_target'];
        }

        if ($PLANET['interplanetary_misil'] <= 0) {
            $error[] = $LNG['ma_no_missiles'];
        }

        if ($anz <= 0) {
            $error[] = $LNG['ma_add_missile_number'];
        }

        /* =========================
         * TARGET USER (SAFE)
         * ========================= */
        if ($target['id_owner'] > 0) {
            $targetUser = GetUserByID(
                $target['id_owner'],
                ['onlinetime', 'banaday', 'urlaubs_modus', 'authattack']
            );
        } else {
            $targetUser = [
                'onlinetime'    => 0,
                'banaday'       => 0,
                'urlaubs_modus' => 0,
                'authattack'    => 0,
            ];
        }

        if (Config::get()->adm_attack == 1 && $targetUser['authattack'] > $USER['authlevel']) {
            $error[] = $LNG['fl_admin_attack'];
        }

        if (!empty($targetUser['urlaubs_modus'])) {
            $error[] = $LNG['fl_in_vacation_player'];
        }

        /* =========================
         * NOOB PROTECTION (SAFE)
         * ========================= */
        $User2Points = $db->selectSingle(
            'SELECT total_points FROM %%USER_POINTS%% WHERE id_owner = :id;',
            [':id' => $target['id_owner']]
        ) ?: ['total_points' => 0];

        $USER += $db->selectSingle(
            'SELECT total_points FROM %%USER_POINTS%% WHERE id_owner = :id;',
            [':id' => $USER['id']]
        ) ?: ['total_points' => 0];

        $IsNoobProtec = CheckNoobProtec($USER, $User2Points, $targetUser);

        if ($IsNoobProtec['NoobPlayer']) {
            $error[] = $LNG['fl_week_player'];
        }

        if ($IsNoobProtec['StrongPlayer']) {
            $error[] = $LNG['fl_strong_player'];
        }

        if (!empty($error)) {
            $this->printMessage(implode("\n", $error));
        }

        /* =========================
         * SEND MISSILES
         * ========================= */
        $Duration = FleetFunctions::GetMIPDuration($PLANET['system'], $targetSystem);

        $fleetArray = [503 => $anz];

        $fleetStartTime = TIMESTAMP + $Duration;
        $fleetStayTime  = $fleetStartTime;
        $fleetEndTime   = $fleetStartTime;

        $fleetResource = [
            901 => 0,
            902 => 0,
            903 => 0,
        ];

        // save planet state before fleet send
        $this->save();

        FleetFunctions::sendFleet(
            $fleetArray,
            10,
            $USER['id'],
            $PLANET['id'],
            $PLANET['galaxy'],
            $PLANET['system'],
            $PLANET['planet'],
            $PLANET['planet_type'],
            $target['id_owner'],
            $target['id'],
            $targetGalaxy,
            $targetSystem,
            $targetPlanet,
            $targetType,
            $fleetResource,
            $fleetStartTime,
            $fleetStayTime,
            $fleetEndTime,
            0,
            $primaryTarget
        );

        $DefenseLabel = ($primaryTarget == 0)
            ? $LNG['ma_all']
            : $LNG['tech'][$primaryTarget];

        $this->printMessage(
            '<b>' . $anz . '</b>' . $LNG['ma_missiles_sended'] . $DefenseLabel
        );
    }
}
