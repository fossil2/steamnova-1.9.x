<?php
/**
 * MOD BONUS
 * @Author Danter14 / U700
 * Fixes & Cleanup: prefix, random ID, logic safety
 * Compatible: 2Moons 1.8 / BitNova
 */

class ShowBonusPage extends AbstractGamePage
{
	public static $requireModule = 0;

	function __construct()
	{
		parent::__construct();
	}

	function show()
	{
		global $USER, $PLANET, $LNG, $resource;

		/**
		 * Urlaubmodus prüfen
		 */
		if ($USER['urlaubs_modus'] != 0) {
			$this->printMessage(
				"Im Urlaubsmodus kann kein Bonus gesammelt werden !!",
				true,
				array('game.php?page=overview', 3)
			);
		}

		$bonus			= true;
		$time_att		= $USER['bonus_attente_time'];
		$metal			= 0;
		$cristal		= 0;
		$deuterium		= 0;
		$darkmatter_add = 0;

		/**
		 * Bonus noch nicht verfügbar
		 */
		if ($USER['bonus_attente_time'] > TIMESTAMP) {
			$bonus = false;
		} else {

			$db		= Database::get();
			$config	= Config::get();

			/**
			 * Anzahl verfügbarer Boni prüfen
			 */
			$sql = "SELECT COUNT(*) as cnt FROM %%BONUS%%";
			$countRow = $db->selectSingle($sql);

			if (empty($countRow) || $countRow['cnt'] < 1) {
				$this->printMessage(
					"Derzeit sind keine Boni verfügbar !!",
					true,
					array('game.php?page=overview', 10)
				);
			}

			/**
			 * Min / Max ID sauber ermitteln
			 */
			$sql = "SELECT MIN(bonusID) as minID, MAX(bonusID) as maxID FROM %%BONUS%%";
			$ids = $db->selectSingle($sql);

			if (empty($ids['minID']) || empty($ids['maxID'])) {
				$this->printMessage(
					"Bonus-Daten fehlerhaft !!",
					true,
					array('game.php?page=overview', 10)
				);
			}

			/**
			 * Zufällige Bonus-ID wählen
			 */
			$rand = mt_rand($ids['minID'], $ids['maxID']);

			/**
			 * Bonusdaten abrufen
			 */
			$sql = "SELECT * FROM %%BONUS%% WHERE bonusID = :id";
			$result = $db->selectSingle($sql, array("id" => $rand));

			/**
			 * Falls ID gelöscht wurde → Fallback
			 */
			if (empty($result)) {
				$result = $db->selectSingle("SELECT * FROM %%BONUS%% ORDER BY bonusID DESC LIMIT 1");
			}

			/**
			 * Ressourcen berechnen
			 */
			$metal			= mt_rand($result['metal_min'], $result['metal_max']) * $config->resource_multiplier;
			$cristal		= mt_rand($result['crystal_min'], $result['crystal_max']) * $config->resource_multiplier;
			$deuterium		= mt_rand($result['deuterium_min'], $result['deuterium_max']) * $config->resource_multiplier;
			$darkmatter_add	= mt_rand($result['darkmatter_min'], $result['darkmatter_max']);

			$time_att	= $result['time_att_bonus'] + TIMESTAMP;
			$attente	= _date($LNG['php_tdformat'], $time_att, $USER['timezone']);

			/**
			 * Wartezeit speichern
			 */
			$sql = "UPDATE %%USERS%% SET bonus_attente_time = :att WHERE id = :uid";
			$db->update($sql, array(
				"att" => $time_att,
				"uid" => $USER['id']
			));

			/**
			 * Ressourcen gutschreiben
			 */
			$PLANET[$resource[901]] += $metal;
			$PLANET[$resource[902]] += $cristal;
			$PLANET[$resource[903]] += $deuterium;
			$USER[$resource[921]]   += $darkmatter_add;
		}

		/**
		 * Template-Ausgabe
		 */
		$this->tplObj->assign_vars(array(
			'bonus'		=> $bonus,
			'bonus_time'=> date("d.m.Y - H:i", $time_att),
			'bonus_m'	=> pretty_number($metal),
			'bonus_c'	=> pretty_number($cristal),
			'bonus_d'	=> pretty_number($deuterium),
			'bonus_dm'	=> pretty_number($darkmatter_add),
		));

		$this->display("page.bonus.default.tpl");
	}
}
