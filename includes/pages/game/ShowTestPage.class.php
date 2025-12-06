<?php


class ShowTestPage extends AbstractGamePage
{

	public static $requireModule = 0;
    
	function __construct() 
	{
		parent::__construct();
	}
	
	function show()
	{

		global $USER, $PLANET, $resource, $pricelist;

		/**
		 * On vérifie si le joueur n'est pas en mode vacance
		 * We check if the player is not in vacation mode
		 */
		if(!$USER['urlaubs_modus'] == 0) {
			$this->printMessage("Impossible de récupérer le bonus en mode vacance !!", true, array('game.php?page=overview', 3));
		}


	
		$this->display("page.Test.default.tpl");
	}
}
