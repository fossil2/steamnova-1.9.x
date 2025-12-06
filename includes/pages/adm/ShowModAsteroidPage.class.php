<?php
/**
 * ADDON MOD ASTEROID
 **/
class ShowModAsteroidPage extends AbstractAdminPage
{
  function __construct(){parent::__construct();}

  function show(){
        global $LNG;
        $config = Config::get(Universe::getEmulated());
        if (!empty($_POST)){
          $config_before = array(
              'asteroid_actif'      => $config->asteroid_actif,
              'asteroid_metal'      => $config->asteroid_metal,
              'asteroid_crystal'    => $config->asteroid_crystal,
              'asteroid_deuterium'  => $config->asteroid_deuterium,
              'asteroid_count'      => $config->asteroid_count,
          );
          
          $asteroid_actif       = isset($_POST['asteroid_actif']) && $_POST['asteroid_actif'] == 'on' ? 1 : 0;
          $asteroid_metal       = HTTP::_GP('asteroid_metal', "");
          $asteroid_crystal     = HTTP::_GP('asteroid_crystal', "");
          $asteroid_deuterium   = HTTP::_GP('asteroid_deuterium', "");
          $asteroid_count       = HTTP::_GP('asteroid_count', "");

          $config_after = array(
              'asteroid_actif'      => $asteroid_actif,
              'asteroid_metal'      => $asteroid_metal,
              'asteroid_crystal'    => $asteroid_crystal,
              'asteroid_deuterium'  => $asteroid_deuterium,
              'asteroid_count'      => $asteroid_count,
          );

          foreach($config_after as $key => $value){
            $config->$key	= $value;
          }
          $config->save();

          $LOG = new Log(3);
          $LOG->target = 0;
          $LOG->old = $config_before;
          $LOG->new = $config_after;
          $LOG->save();
        }

        $this->assign(array(
          'asteroid_actif'      => $config->asteroid_actif,
          'asteroid_metal'      => $config->asteroid_metal,
          'asteroid_crystal'    => $config->asteroid_crystal,
          'asteroid_deuterium'  => $config->asteroid_deuterium,
          'asteroid_count'      => $config->asteroid_count,
        ));

    $this->display('page.mods.asteroid.tpl');
  }
}

 ?>
