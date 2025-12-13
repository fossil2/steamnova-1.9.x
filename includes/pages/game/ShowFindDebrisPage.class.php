<?php
/**
 * MOD FIND DEBRIS
 * @Author Danter14 / U700
 * Erweiterung: Recycler-Check
 * Compatible: 2Moons 1.8 / BitNova
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
		 * Urlaubmodus prüfen
		 */
		if ($USER['urlaubs_modus'] != 0) {
			$this->printMessage(
				$LNG['find_deb_mv'],
				true,
				array('game.php?page=overview', 3)
			);
		}

		
		$recyclerID = 219;
		if (empty($PLANET[$resource[$recyclerID]]) || $PLANET[$resource[$recyclerID]] < 1) {
			$this->printMessage(
				$LNG['find_deb_norecycler'],
				true,
				array('game.php?page=overview', 3)
			);
		}

		$db = Database::get();

		
		$laboratoryLevel = (int)$PLANET['laboratory'];
		$range = ($laboratoryLevel <= 0) ? 1 : $laboratoryLevel * 4;

		$systemMin = max(1, $PLANET['system'] - $range);
		$systemMax = $PLANET['system'] + $range;

		
		$sql = "
			SELECT id, der_metal, der_crystal, galaxy, system, planet
			FROM %%PLANETS%%
			WHERE
				(der_metal > 0 OR der_crystal > 0)
				AND system BETWEEN :systemMin AND :systemMax
				AND galaxy = :galaxy
				AND planet_type = '1'
		";

		$debrisList = $db->select($sql, [
			":systemMin" => $systemMin,
			":systemMax" => $systemMax,
			":galaxy"	 => $PLANET['galaxy'],
		]);

		
		$table = "
		<table style='background:#111A21;box-shadow:inset 0 1px 1px rgba(33,49,64,.75);'
			width='100%' cellpadding='3' cellspacing='1' class='center'>
			<tbody>
				<tr>
					<td>{$LNG['find_deb_coord']}</td>
					<td>" . sprintf($LNG['find_deb_metal'], $LNG['tech'][901]) . "</td>
					<td>" . sprintf($LNG['find_deb_crystal'], $LNG['tech'][902]) . "</td>
					<td></td>
				</tr>
		";

		if (!empty($debrisList)) {
			foreach ($debrisList as $debris) {

				$GRecNeeded = min(
					ceil(($debris['der_metal'] + $debris['der_crystal']) / $pricelist[219]['capacity']),
					$PLANET[$resource[219]]
				);

				$table .= "
				<tr>
					<td><strong>[{$debris['galaxy']}.{$debris['system']}.{$debris['planet']}]</strong></td>
					<td>" . number_format($debris['der_metal']) . "</td>
					<td>" . number_format($debris['der_crystal']) . "</td>
					<td>
						<a href='javascript:doit(8,{$debris['id']});'
						   title='{$GRecNeeded} Recycler benötigt'
						   style='background:linear-gradient(180deg,#85ef00,#0e2d00);
						   border-radius:50px;color:#fff;font-weight:600;
						   text-decoration:none;display:block;text-align:center;'>
							{$LNG['find_deb_btn']}
						</a>
					</td>
				</tr>";
			}
		} else {
			$table .= "
			<tr>
				<td colspan='4' style='color:#d32a29;font-weight:900;padding:20px;'>
					{$LNG['find_deb_empty']}
				</td>
			</tr>";
		}

		$table .= "</tbody></table>";

		$this->tplObj->assign_vars([
			'range' => $range,
			'debris' => $table,
			'user_maxfleetsettings' => $USER['settings_fleetactions'],
		]);

		$this->display("page.finddebris.default.tpl");
	}
}
