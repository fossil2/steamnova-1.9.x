<?php

/**
 *  2Moons
 *   by Jan-Otto Kröpke 2009-2016
 *
 * For the full copyright and license information, please view the LICENSE
 *
 * @package 2Moons
 * @author Jan-Otto Kröpke <slaver7@gmail.com>
 * @copyright 2009 Lucky
 * @copyright 2016 Jan-Otto Kröpke <slaver7@gmail.com>
 * @licence MIT
 * @version 1.8.x Koray Karakuş <koraykarakus@yahoo.com>
 * @link https://github.com/jkroepke/2Moons
 */


class ShowRaportPage extends AbstractGamePage
{
    public static $requireModule = 0;
    protected $disableEcoSystem = true;

    function __construct()
    {
        parent::__construct();
    }

    private function BCWrapperPreRev2321($combatReport)
    {
        if(isset($combatReport['moon']['desfail']))
        {
            $combatReport['moon'] = array(
                'moonName' => $combatReport['moon']['name'],
                'moonChance' => $combatReport['moon']['chance'],
                'moonDestroySuccess' => !$combatReport['moon']['desfail'],
                'fleetDestroyChance' => $combatReport['moon']['chance2'],
                'fleetDestroySuccess' => !$combatReport['moon']['fleetfail']
            );
        }
        elseif(isset($combatReport['moon'][0]))
        {
            $combatReport['moon'] = array(
                'moonName' => $combatReport['moon'][1],
                'moonChance' => $combatReport['moon'][0],
                'moonDestroySuccess' => !$combatReport['moon'][2],
                'fleetDestroyChance' => $combatReport['moon'][3],
                'fleetDestroySuccess' => !$combatReport['moon'][4]
            );
        }

        if(isset($combatReport['simu']))
        {
            $combatReport['additionalInfo'] = $combatReport['simu'];
        }

        if(isset($combatReport['debris'][0]))
        {
            $combatReport['debris'] = array(
                901 => $combatReport['debris'][0],
                902 => $combatReport['debris'][1]
            );
        }

        if (!empty($combatReport['steal']['metal']))
        {
            $combatReport['steal'] = array(
                901 => $combatReport['steal']['metal'],
                902 => $combatReport['steal']['crystal'],
                903 => $combatReport['steal']['deuterium']
            );
        }

        return $combatReport;
    }

    function battlehall()
    {
        global $LNG, $USER;

        $LNG->includeData(array('FLEET'));
        $this->setWindow('popup');

        $db = Database::get();
        $RID = HTTP::_GP('raport', '');

        // Neue Abfrage für deine Tabellenstruktur
        $sql = "SELECT 
                r.raport, 
                r.time,
                (
                    SELECT GROUP_CONCAT(u.username SEPARATOR ' & ') 
                    FROM users u
                    INNER JOIN users_to_topkb ut ON u.id = ut.uid
                    WHERE ut.rid = t.rid AND ut.role = 1
                ) as attacker,
                (
                    SELECT GROUP_CONCAT(u.username SEPARATOR ' & ') 
                    FROM users u
                    INNER JOIN users_to_topkb ut ON u.id = ut.uid
                    WHERE ut.rid = t.rid AND ut.role = 2
                ) as defender
                FROM topkb t
                INNER JOIN raports r ON t.rid = r.rid
                WHERE t.rid = :reportID
                LIMIT 1";

        $reportData = $db->selectSingle($sql, array(
            ':reportID' => $RID
        ));

        if(empty($reportData)) {
            $this->printMessage($LNG['sys_raport_not_found']);
            return;
        }

        // Sicherer Zugriff auf die Daten
        $attacker = isset($reportData['attacker']) ? $reportData['attacker'] : '';
        $defender = isset($reportData['defender']) ? $reportData['defender'] : '';
        $Info = array($attacker, $defender);

        if(!isset($reportData['raport'])) {
            $this->printMessage($LNG['sys_raport_not_found']);
            return;
        }

        $combatReport = unserialize($reportData['raport']);
        if($combatReport === false) {
            $this->printMessage('Ungültiges Berichtsformat');
            return;
        }

        $combatReport['time'] = _date($LNG['php_tdformat'], $reportData['time'], $USER['timezone']);
        $combatReport = $this->BCWrapperPreRev2321($combatReport);

        $this->assign(array(
            'Raport' => $combatReport,
            'Info' => $Info,
            'pageTitle' => $LNG['lm_topkb']
        ));

        $this->display('shared.mission.raport.tpl');
    }

    function show()
    {
        global $LNG, $USER;

        $LNG->includeData(array('FLEET'));
        $this->setWindow('popup');

        $db = Database::get();
        $RID = HTTP::_GP('raport', '');

        // Angepasst für raports-Tabelle
        $sql = "SELECT raport, attacker, defender FROM %%RW%% WHERE rid = :reportID LIMIT 1";
        $reportData = $db->selectSingle($sql, array(
            ':reportID' => $RID
        ));

        if(empty($reportData)) {
            $this->printMessage($LNG['sys_raport_not_found']);
            return;
        }

        // BC für pre r2484
        $isAttacker = empty($reportData['attacker']) || in_array($USER['id'], explode(",", $reportData['attacker']));
        $isDefender = empty($reportData['defender']) || in_array($USER['id'], explode(",", $reportData['defender']));

        $combatReport = unserialize($reportData['raport']);
        if($isAttacker && !$isDefender && $combatReport['result'] == 'r' && count($combatReport['rounds']) <= 2) {
            $this->printMessage($LNG['sys_raport_lost_contact']);
            return;
        }

        $combatReport['time'] = _date($LNG['php_tdformat'], $combatReport['time'], $USER['timezone']);
        $combatReport = $this->BCWrapperPreRev2321($combatReport);

        $this->assign(array(
            'Raport' => $combatReport,
            'pageTitle' => $LNG['sys_mess_attack_report']
        ));

        $this->display('shared.mission.raport.tpl');
    }
}