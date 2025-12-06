<?php
/**
 * MOD Anomalie
 * @Author U700
 * @date 01/02/2025
 * @version 2moons > 1.8
 */
class ShowAnomaliePage extends AbstractGamePage
{
	public static $requireModule = 0;
    
	function __construct(){parent::__construct();}
	
	function show()
	{
		global $USER, $PLANET, $LNG, $resource, $pricelist;
		
		if(!$USER['urlaubs_modus'] == 0){
			$this->printMessage("Unable to collect bonus in vacation mode !!", true, array('game.php?page=overview', 3));
		}
		$this->tplObj->loadscript('jquery.countdown.js');
		$bonus_secs = 0;
		$bonus = true;
		$bonus_time = "";
		if($USER['bonus_attente_time'] > TIMESTAMP){
			$bonus_time = date("d.m.y H:i", $USER['bonus_attente_time']);
			$bonus = false;
			$bonus_secs = $USER['bonus_attente_time'] - TIMESTAMP;
		}
		$this->tplObj->assign_vars([
			'bonus' => $bonus,
			'bonus_time' => $bonus_time,
			'bonus_secs'	=> $bonus_secs,
		]);
		$this->display("page.anomalie.default.tpl");
	}
}
