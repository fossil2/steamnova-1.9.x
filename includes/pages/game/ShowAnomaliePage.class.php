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

    function __construct()
    {
        parent::__construct();
    }

    function show()
    {
        global $USER, $PLANET, $LNG, $resource, $pricelist, $config;

        if (!$USER['urlaubs_modus'] == 0) {
            $this->printMessage(
                "Unable to collect bonus in vacation mode !!",
                true,
                array('game.php?page=overview', 3)
            );
        }

        $this->tplObj->loadscript('jquery.countdown.js');


        /* =========================
           BONUS
           ========================= */

        $bonus_secs = 0;
        $bonus = true;
        $bonus_time = '';

        if ((int)$USER['bonus_attente_time'] > TIMESTAMP) {
            $bonus_time = date(
                'd.m.y H:i',
                (int)$USER['bonus_attente_time']
            );

            $bonus = false;

            $bonus_secs =
                (int)$USER['bonus_attente_time'] - TIMESTAMP;
        }


        /* =========================
           COLLECT MINES
           ========================= */

        $collect_mine_secs = 0;
        $collect_mine_time = '';
        $collect_mine_ready = true;

        $collectMineCooldown =
            (int)$config->collect_mine_time_minutes * 60;

        $lastCollectMine =
            (int)$USER['last_collect_mine_time'];

        $collectMineNext =
            $lastCollectMine + $collectMineCooldown;

        if (
            $lastCollectMine > 0
            && $collectMineNext > TIMESTAMP
        ) {
            $collect_mine_ready = false;

            $collect_mine_secs =
                $collectMineNext - TIMESTAMP;

            $collect_mine_time = date(
                'd.m.y H:i',
                $collectMineNext
            );
        }


        /* =========================
           TEMPLATE DATEN
           ========================= */

        $this->tplObj->assign_vars([

            'bonus' => $bonus,
            'bonus_time' => $bonus_time,
            'bonus_secs' => $bonus_secs,

            'collect_mines_active' =>
                isModuleAvailable(MODULE_COLLECT_MINES),

            'collect_mine_dm_cost' =>
                (int)$config->collect_mine_dm_cost,

            'collect_mine_ready' =>
                $collect_mine_ready,

            'collect_mine_secs' =>
                $collect_mine_secs,

            'collect_mine_time' =>
                $collect_mine_time,
        ]);

        $this->display('page.anomalie.default.tpl');
    }
}