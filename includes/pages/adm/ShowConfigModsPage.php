<?php
/**
 * 2moons-2.0
 * The project is reserved for its owner for any bug or question you can contact him
 * Created by 2moons-2.0.
 * User: Danter14 (danter14000@gmail.com)
 * Copyright: 2019 - 2023 - Danter14
 * GitHub: https://github.com/HikeGame/Spaccon-1.0
 * Date: 17/01/2019
 * File: ShowConfigModsPage.php
 * Version: 1.0.0
 * ADDONS MOD Asteroids
 */

/**if (!allowedTo(str_replace(array(dirname(__FILE__), '\\', '/', '.php'), '', __FILE__))) throw new Exception("Permission error!");
*/
class ShowConfigModsPage extends AbstractAdminPage
{
    global $LNG;
    $config = Config::get(Universe::getEmulated());

    if (!empty($_POST))
    {
        $config_before = array(
            'expedition_limit_res'				=> $config->expedition_limit_res,
            'expedition_limit_res_active'		=> $config->expedition_limit_res_active,
            // ADDON
            'asteroid_actif'                    => $config->asteroid_actif,
            'asteroid_metal'                    => $config->asteroid_metal,
            'asteroid_crystal'                  => $config->asteroid_crystal,
            'asteroid_deuterium'                => $config->asteroid_deuterium,
            'asteroid_count'                    => $config->asteroid_count,
        );

        $expedition_limit_res_active 		= isset($_POST['expedition_limit_res_active']) && $_POST['expedition_limit_res_active'] == 'on' ? 1 : 0;

        $expedition_limit_res				= HTTP::_GP('expedition_limit_res', 0);

        /**
         * ADDON MOD ASTEROIDS
         */
        $asteroid_actif 		    = isset($_POST['asteroid_actif']) && $_POST['asteroid_actif'] == 'on' ? 1 : 0;
        $asteroid_metal			    = HTTP::_GP('asteroid_metal', "");
        $asteroid_crystal			= HTTP::_GP('asteroid_crystal', "");
        $asteroid_deuterium			= HTTP::_GP('asteroid_deuterium', "");
        $asteroid_count			    = HTTP::_GP('asteroid_count', "");

        $config_after = array(
            'expedition_limit_res'				=> $expedition_limit_res,
            'expedition_limit_res_active'		=> $expedition_limit_res_active,
            // ADDON
            'asteroid_actif'                    => $asteroid_actif,
            'asteroid_metal'                    => $asteroid_metal,
            'asteroid_crystal'                  => $asteroid_crystal,
            'asteroid_deuterium'                => $asteroid_deuterium,
            'asteroid_count'                    => $asteroid_count,
        );

        foreach($config_after as $key => $value)
        {
            $config->$key	= $value;
        }
        $config->save();

        $LOG = new Log(3);
        $LOG->target = 0;
        $LOG->old = $config_before;
        $LOG->new = $config_after;
        $LOG->save();
    }

    /*
    $template	= new template();

    $template->assign_vars(array(
        'expedition_limit_res'					=> $config->expedition_limit_res,
        'expedition_limit_res_active'			=> $config->expedition_limit_res_active,
        // ADDON
        'asteroid_actif'                        => $config->asteroid_actif,
        'asteroid_metal'                        => $config->asteroid_metal,
        'asteroid_crystal'                      => $config->asteroid_crystal,
        'asteroid_deuterium'                    => $config->asteroid_deuterium,
        'asteroid_count'                        => $config->asteroid_count,
    ));

    $template->show('ConfigModsBody.tpl');
    */
    $this->assign(array(
        'asteroid_actif'                        => $config->asteroid_actif,
        'asteroid_metal'                        => $config->asteroid_metal,
        'asteroid_crystal'                      => $config->asteroid_crystal,
        'asteroid_deuterium'                    => $config->asteroid_deuterium,
        'asteroid_count'                        => $config->asteroid_count,
      ));
      $this->show('ConfigModsBody.tpl');
}