<?php
/**
 * MOD BONUS
 * @Author Danter14 / U700
 * @date 01/02/2025
 * @version 2moons > 1.8
 */
class ShowBonusPage extends AbstractGamePage
{
	public static $requireModule = 0;
	
	function __construct(){parent::__construct();}

	function show(){
		global $USER, $PLANET, $LNG, $resource;
		/**
		 * Prüfen, ob Player im Urlaubsmodus
		 */
		if(!$USER['urlaubs_modus'] == 0){
			$this->printMessage("Im Urlaubsmodus kann kein Bonus gesammelt werden !!", true, array('game.php?page=overview', 3));
		}
		
		$bonus = true;
		$time_att = $USER['bonus_attente_time'];
		$metal = 0;
		$cristal = 0;
		$deuterium = 0;
		$darkmatter_add = 0;

		// copy von zeile 79
		if($USER['bonus_attente_time'] > TIMESTAMP){
			$bonus = false;
		}else{
			// Connect BD
			$db = Database::get();

			// Connect Config
			$config	= Config::get();

			/** 
			* Dunkle Materie des Spielers aus DB
			* Player's Dark Matter Recovery
			*/
			$darkmatter = $USER['darkmatter'];

			/** 
			 * Payback for the number of bonus rows in the table
			 * Rückzahlung für die Anzahl der Bonuszeilen in der Tabelle
			 */
			$sql = "SELECT * FROM bonus ORDER BY bonusID DESC";
			$result_select = $db->select($sql);
			$restult_count = $db->rowCount($result_select);

			/**
			 * We check if there are bonuses available in the database.. sleep von 3 auf 10
			 */
			if($restult_count < 1){
				$this->printMessage("Derzeit sind keine Boni verfügbar !!", true, array('game.php?page=overview', 10));
			}

			/**
			 * We check if the player has already collected his bonus
			 * Wir prüfen, ob in der Datenbank Boni verfügbar sind.. sleep von 3 auf 10
			 */
			// if($USER['bonus_attente_time'] > TIMESTAMP) {
			// 	$datum = date("d.m.Y - H:i", $USER['bonus_attente_time']);
			// 	$this->printMessage("Ihre Wartezeit ist noch nicht abgelaufen !!<br>Nächster Bonus ab: ".$datum, true, array('game.php?page=anomalie', 10));
			// }

			/**
			 * We get the smallest id of our table
			 * Wir erhalten die kleinste ID unserer Tabelle
			 */
			foreach($result_select as $response){
				$id_min = $response['bonusID'];
			}

			/**
			 * We randomly choose one of the bonus
			 * Wir wählen zufällig einen der Boni aus
			 */
			$rand = mt_rand($id_min , $restult_count);

			/**
			 * We retrieve the bonus information
			 * Wir rufen die Bonusinformationen ab
			 */
			$sql_single = "SELECT * FROM bonus WHERE bonusID = :id";
			$result = $db->selectSingle($sql_single, ["id" => $rand ]);

			/**
			 * We give the resources randomly between our min and max * the speed of the server resources
			 * Wir geben die Ressourcen zufällig zwischen unserem Minimum und Maximum an. * Die Geschwindigkeit der Serverressourcen
			 */
			$metal = mt_rand($result["metal_min"], $result["metal_max"]) * $config->resource_multiplier;
			$cristal = mt_rand($result["crystal_min"], $result["crystal_max"]) * $config->resource_multiplier;
			$deuterium = mt_rand($result["deuterium_min"], $result["deuterium_max"]) * $config->resource_multiplier;
			$darkmatter_add = mt_rand($result["darkmatter_min"], $result["darkmatter_max"]);
			$time_att = $result["time_att_bonus"] + TIMESTAMP;
			$attente = _date($LNG['php_tdformat'], $time_att, $USER['timezone']);


			/**
			 * We add the waiting time for the next bonus in the users table
			 * Wir fügen die Wartezeit für den nächsten Bonus in der Benutzertabelle hinzu
			 */
			$sql_update = "UPDATE %%USERS%% SET bonus_attente_time = :att WHERE id = :userId";
			$db->update($sql_update, ["att" => $time_att, "userId" => $USER['id']]);

			/**
			 * We add the resources received randomly previously in the BDD (table planets and users)
			 * Wir fügen die zuvor zufällig erhaltenen Ressourcen im BDD hinzu (Tabelle Planeten und Benutzer)
			 */
			$PLANET[$resource[901]] += $metal;
			$PLANET[$resource[902]] += $cristal;
			$PLANET[$resource[903]] += $deuterium;
			$USER[$resource[921]] += $darkmatter_add;

			/**
			 * The player is informed by a message in the game of these bonus wins
			 * Der Spieler wird durch eine Nachricht im Spiel über diese Bonusgewinne informiert
			 */
			// $this->printMessage(
			// 	$LNG['tech'][901]." :".pretty_number($metal).
			// 	"<br>".$LNG['tech'][902]." :".pretty_number($cristal).
			// 	"<br>".$LNG['tech'][903]." :".pretty_number($deuterium).
			// 	"<br>".$LNG['tech'][921]." :".pretty_number($darkmatter_add).
			// 	"<br>Nächster Bonus : ".$attente, true, array('game.php?page=overview', 60));
				// 	"<br>Nächster Bonus : ".$attente, true, array('game.php?page=overview', 3)

		}
			$this->tplObj->assign_vars([
				'bonus' => $bonus,
				'bonus_time' => date("d.m.Y - H:i", $time_att),
				'bonus_m' => pretty_number($metal),
				'bonus_c' => pretty_number($cristal),
				'bonus_d' => pretty_number($deuterium),
				'bonus_dm' => pretty_number($darkmatter_add),
			]);
			$this->display("page.bonus.default.tpl");
		// }
	}
}