<?php
/**
 * MOD FIND DEBRIS
 * @Author Danter14 / U700
 * @date 01/02/2025
 * @version 2moons > 1.8
 */
class ShowFindDebrisPage extends AbstractGamePage
{

	public static $requireModule = 0;

	function __construct(){parent::__construct();}

	function show()
	{
		global $USER, $PLANET, $resource, $pricelist, $LNG;
		/**
		 * We check if the player is not in vacation mode
		 */
		if(!$USER['urlaubs_modus'] == 0){
			$this->printMessage($LNG['find_deb_mv'], true, array('game.php?page=overview', 3));
		}
		
		// Connexion BDD
		$db = Database::get();

		$mode = HTTP::_GP('y', '');
		$table = "";

		$stufe_laboratory = $PLANET['laboratory'];
		$range = $stufe_laboratory == 0 ? 1 : $stufe_laboratory * 4;
		//$range = $PLANET['laboratory'] == 0 ? 1 : $PLANET['laboratory'] * 4;
		// $PLANET['hangar'] in laboratory geaendert

		// if ($mode == '1') {
			$sql_find = "SELECT id, der_metal, der_crystal, galaxy, system, planet, planet_type FROM %%PLANETS%% WHERE (`der_metal` > 0 OR `der_crystal` > 0) AND (`system` > :systemMin AND `system` < :systemMax) AND `galaxy` = :galaxy and `planet_type` = '1'";
			$tab_params = [
				":systemMin" => $PLANET['system'] - $range,
				":systemMax" => $PLANET['system'] + $range,
				":galaxy" => $PLANET['galaxy'],
			];
			$cautares = $db->select($sql_find, $tab_params);
			$table = "
			<table style='background-color: transparent; background: #111A21; box-shadow: inset 0 1px 1px rgba(33, 49, 64, 0.75);' width='100%' border='0' cellpadding='3' cellspacing='1' class='center'>
				<tbody style='border-color: transparent; border-style: solid; border-width: 2px;'>
				<tr>
					<td>" . $LNG['find_deb_coord'] . "</td>
					<td>" . sprintf($LNG['find_deb_metal'], $LNG['tech'][901]) . "</td>
					<td>" . sprintf($LNG['find_deb_crystal'], $LNG['tech'][902]) . "</td>
					<td></td>
				</tr>
			";

			if ($db->rowCount($cautares) > 0){
				foreach ($cautares as $cautare){
					$GRecNeeded = min(ceil(($cautare['der_metal'] + $cautare['der_crystal']) / $pricelist[219]['capacity']), $PLANET[$resource[219]]);
					$table .= "
					<tr style='border-top-width: 5px;'>
						<td><span style='font-weight: bold;'>[" . $cautare['galaxy'] . "." . $cautare['system'] . "." . $cautare['planet'] . "]</span></td>
						<td>" . number_format($cautare['der_metal']) . "</td>
						<td>" . number_format($cautare['der_crystal']) . "</td>
						<td><a href='javascript:doit(8," . $cautare['id'] . ");' style='
							background: linear-gradient(180deg, #85ef00, #0e2d00);
							border-radius: 50px;
							color: #fff;
							font-weight: 600;
							text-decoration: none;
							text-align: center;
							text-shadow: -1px 1px 5px #246a05;
							width: 100%;
							padding: 2px 0;
							display: block;
							cursor: pointer;
						'>" . $LNG['find_deb_btn'] . "</a></td>
					</tr>";
				}
			}else{
				$table .= "<tr><td colspan='5' style='color: #d32a29; font-weight: 900; padding: 20px;'>" . $LNG['find_deb_empty'] . "</td></tr>";
			}
			$table .= "</tbody></table>";
		// }
		$this->tplObj->assign_vars([
			'range' => $range,
			'debris' => $table,
			'user_maxfleetsettings' => $USER['settings_fleetactions'],
		]);
		$this->display("page.finddebris.default.tpl");
	}
}