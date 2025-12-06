<?php

/**
 * MOD FIND DEBRIS
 * @Author Danter14
 * @date 20/10/2022
 * @version 2moons > 1.8
 */

class ShowFindDebrisPage extends AbstractGamePage
{

	public static $requireModule = 0;

	function __construct()
	{
		parent::__construct();
	}

	function show()
	{

		global $USER, $PLANET, $resource, $pricelist, $LNG;

		/**
		 * On vérifie si le joueur n'est pas en mode vacance
		 * We check if the player is not in vacation mode
		 */
		if (!$USER['urlaubs_modus'] == 0) {
			$this->printMessage($LNG['find_deb_mv'], true, array('game.php?page=overview', 3));
		}

		// Connexion BDD
		$db = Database::get();

		$mode = HTTP::_GP('y', '');
		$table = "";

		$range = $PLANET['hangar'] == 0 ? 1 : $PLANET['hangar'] * 4;

		if ($mode == '1') {
			$sql_find = "SELECT id, der_metal, der_crystal, galaxy, system, planet, planet_type FROM %%PLANETS%% WHERE (`der_metal` > 0 OR `der_crystal` > 0) AND (`system` > :systemMin AND `system` < :systemMax) AND `galaxy` = :galaxy and `planet_type` = '1'";
			$tab_params = [
				":systemMin" => $PLANET['system'] - $range,
				":systemMax" => $PLANET['system'] + $range,
				":galaxy" => $PLANET['galaxy'],
			];
			$cautares = $db->select($sql_find, $tab_params);
			$table = "
			<table width='100%' border='0' cellpadding='0' cellspacing='1' class='center'>
				<tr>
					<td>" . $LNG['find_deb_coord'] . "</td>
					<td>" . sprintf($LNG['find_deb_metal'], $LNG['tech'][901]) . "</td>
					<td>" . sprintf($LNG['find_deb_crystal'], $LNG['tech'][902]) . "</td>
					<td>#</td>
				</tr>
			";

			if ($db->rowCount($cautares) > 0) {

				foreach ($cautares as $cautare) {
					$GRecNeeded = min(ceil(($cautare['der_metal'] + $cautare['der_crystal']) / $pricelist[219]['capacity']), $PLANET[$resource[219]]);
					$table .= "
					<tr>
						<td><span style='font-weight: bold;'>[" . $cautare['galaxy'] . "." . $cautare['system'] . "." . $cautare['planet'] . "]</span></td>
						<td>" . number_format($cautare['der_metal']) . "</td>
						<td>" . number_format($cautare['der_crystal']) . "</td>
						<td><a href='javascript:doit(8," . $cautare['id'] . ");' style='color: green;'>" . $LNG['find_deb_btn'] . "</a></td>
					</tr>";
				}
			} else {
				$table .= "<tr><td colspan='5'>" . $LNG['find_deb_empty'] . "</td></tr>";
			}

			$table .= "</table>";
		}

		$this->tplObj->assign_vars([
			'range' => $range,
			'debris' => $table,
			'user_maxfleetsettings' => $USER['settings_fleetactions'],
		]);

		$this->display("page.finddebris.default.tpl");
	}
}
