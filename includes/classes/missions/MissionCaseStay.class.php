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

class MissionCaseStay extends MissionFunctions implements Mission
{
    function __construct($Fleet)
    {
        $this->_fleet = $Fleet;
    }

    function TargetEvent()
    {
        $db = Database::get();

        // Check if target planet still exists
        $sql = 'SELECT * FROM %%PLANETS%% WHERE id = :planetId;';
        $targetPlanet = $db->selectSingle($sql, [
            ':planetId' => $this->_fleet['fleet_end_id'],
        ]);

        // Return fleet if target planet was deleted
        if ($targetPlanet == false) {
            $this->setState(FLEET_RETURN);
            $this->SaveFleet();
            return;
        }

        $sql = 'SELECT * FROM %%USERS%% WHERE id = :userId;';
        $senderUser = $db->selectSingle($sql, [
            ':userId' => $this->_fleet['fleet_owner']
        ]);

        $senderUser['factor'] = getFactors($senderUser, 'basic', $this->_fleet['fleet_start_time']);

        $fleetArray = FleetFunctions::unserialize($this->_fleet['fleet_array']);
        $duration = $this->_fleet['fleet_start_time'] - $this->_fleet['start_time'];

        $SpeedFactor = FleetFunctions::GetGameSpeedFactor();
        $distance = FleetFunctions::GetTargetDistance(
            [$this->_fleet['fleet_start_galaxy'], $this->_fleet['fleet_start_system'], $this->_fleet['fleet_start_planet']],
            [$this->_fleet['fleet_end_galaxy'], $this->_fleet['fleet_end_system'], $this->_fleet['fleet_end_planet']]
        );

        $consumption = FleetFunctions::GetFleetConsumption($fleetArray, $duration, $distance, $senderUser, $SpeedFactor);

        $this->UpdateFleet('fleet_resource_deuterium', $this->_fleet['fleet_resource_deuterium'] + $consumption / 2);

        $LNG = $this->getLanguage($senderUser['lang']);
        $TargetUserID = $this->_fleet['fleet_target_owner'];

        $sql = 'SELECT `name` FROM %%PLANETS%% WHERE `id` = :planetId;';
        $startPlanetName = $db->selectSingle($sql, [
            ':planetId' => $this->_fleet['fleet_start_id'],
        ], 'name');

        $stationShips = "";
        foreach ($fleetArray as $shipID => $amount) {
            $stationShips .= "<br>" . $LNG['tech'][$shipID] . ": " . $amount;
        }

  $TargetMessage = sprintf(
    $LNG['sys_stat_mess'],
    $startPlanetName,  // Achtung: Kein Tippfehler mehr!
    GetStartAddressLink($this->_fleet, ''),
    $LNG['type_planet_' . $this->_fleet['fleet_end_type']],
    $targetPlanet['name'],
    GetTargetAddressLink($this->_fleet, ''),
    pretty_number($this->_fleet['fleet_resource_metal']),   // Metall
    $LNG['tech'][901],                                     // "Metall" in Sprachdatei
    pretty_number($this->_fleet['fleet_resource_crystal']), // Kristall
    $LNG['tech'][902],                                     // "Kristall" in Sprachdatei
    pretty_number($this->_fleet['fleet_resource_deuterium']), // Deuterium
    $LNG['tech'][903],                                     // "Deuterium" in Sprachdatei
    $stationShips
);

        PlayerUtil::sendMessage(
            $TargetUserID,
            0,
            $LNG['sys_mess_tower'],
            5, // MESSAGE_TYPE_TRANSPORT
            $LNG['sys_stat_mess_stay'],
            $TargetMessage,
            $this->_fleet['fleet_start_time'],
            1,
            $this->_fleet['fleet_universe']
        );

        $this->savePlanetProduction($this->_fleet['fleet_end_id'], $this->_fleet['fleet_start_time']);
        $this->RestoreFleet(false);
    }

    function EndStayEvent()
    {
        return;
    }

    function ReturnEvent()
    {
        $LNG = $this->getLanguage(null, $this->_fleet['fleet_owner']);

        $db = Database::get();
        $sql = 'SELECT `name` FROM %%PLANETS%% WHERE id = :planetId;';
        $targetPlanet = $db->selectSingle($sql, [
            ':planetId' => $this->_fleet['fleet_start_id'],
        ]);

        $stationShips = "";
        $fleetArray = FleetFunctions::unserialize($this->_fleet['fleet_array']);

        foreach ($fleetArray as $shipID => $amount) {
            $stationShips .= "<br>" . $LNG['tech'][$shipID] . ": " . $amount;
        }

        $Message = sprintf(
            $LNG['fl_returning1'],
            $LNG['type_planet_' . $this->_fleet['fleet_start_type']],
            $targetPlanet['name'],
            GetStartAddressLink($this->_fleet, ''),
            pretty_number($this->_fleet['fleet_resource_metal']),
            $LNG['tech'][901],
            pretty_number($this->_fleet['fleet_resource_crystal']),
            $LNG['tech'][902],
            pretty_number($this->_fleet['fleet_resource_deuterium']),
            $LNG['tech'][903],
            $stationShips
        );

        PlayerUtil::sendMessage(
            $this->_fleet['fleet_owner'],
            0,
            $LNG['sys_mess_tower'],
            4, // MESSAGE_TYPE_SYSTEM
            $LNG['sys_mess_fleetback'],
            $Message,
            $this->_fleet['fleet_end_time'],
            1,
            $this->_fleet['fleet_universe']
        );

        $this->savePlanetProduction($this->_fleet['fleet_start_id'], $this->_fleet['fleet_end_time']);
        $this->RestoreFleet();
    }
}