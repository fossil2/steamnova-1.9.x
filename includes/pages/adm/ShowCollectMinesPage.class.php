<?php

/**
 *  2Moons
 *   by Jan-Otto Kröpke 2009-2016
 *
 * @package 2Moons
 * @version 1.8.x
 */

class ShowCollectMinesPage extends AbstractAdminPage
{
    function __construct()
    {
        parent::__construct();
    }

    function show()
    {
        global $config;

        $this->assign(array(
            'collect_mines_under_attack' => (int)$config->collect_mines_under_attack,
            'collect_mine_time_minutes' => (int)$config->collect_mine_time_minutes,
            'collect_mine_dm_cost'      => (int)$config->collect_mine_dm_cost,
        ));

        $this->display('page.collect_mines.default.tpl');
    }

    function saveSettings()
    {
        global $LNG, $config;

        // Backup old config for log
        $config_before = array(
            'collect_mines_under_attack' => (int)$config->collect_mines_under_attack,
            'collect_mine_time_minutes' => (int)$config->collect_mine_time_minutes,
            'collect_mine_dm_cost'      => (int)$config->collect_mine_dm_cost,
        );

        // Read POST values
        $collect_mines_under_attack = (HTTP::_GP('collect_mines_under_attack', 'off') === 'on') ? 1 : 0;
        $collect_mine_time_minutes = max(1, (int)HTTP::_GP('collect_mine_time_minutes', 30));
        $collect_mine_dm_cost      = max(0, (int)HTTP::_GP('collect_mine_dm_cost', 50));

        // New config values
        $config_after = array(
            'collect_mines_under_attack' => $collect_mines_under_attack,
            'collect_mine_time_minutes' => $collect_mine_time_minutes,
            'collect_mine_dm_cost'      => $collect_mine_dm_cost,
        );

        // Save config
        foreach ($config_after as $key => $value) {
            $config->$key = $value;
        }
        $config->save();

        // Admin log
        $LOG = new Log(3);
        $LOG->target = 1;
        $LOG->old = $config_before;
        $LOG->new = $config_after;
        $LOG->save();

        // Redirect
        $redirectButton[] = array(
            'url'   => 'admin.php?page=collectMines&mode=show',
            'label' => $LNG['uvs_back'],
        );

        $this->printMessage($LNG['settings_successful'], $redirectButton);
    }
}
