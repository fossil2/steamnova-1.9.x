<?php

class ShowBotsTasksPage extends AbstractAdminPage
{
    function __construct()
    {
        parent::__construct();
    }

    function show()
    {
        $this->display('page.bots.tasks.tpl');
    }

    function runTask()
    {
        $task = HTTP::_GP('task', '', true);

        $allowedTasks = [
            'attack_idlers',
            'attack_players',
            'autobuilder',
            'autorefresher',
            'build_ships',
            'colonize_planets',
            'remove_messages',
            'send_expedition',
            'send_recyclers'
        ];

        if (!in_array($task, $allowedTasks)) {
            $this->printMessage('Ungültiger Task');
        }

        $taskFile = 'includes/bot_tasks/' . $task . '.php';

        if (!file_exists($taskFile)) {
            $this->printMessage('Task-Datei nicht gefunden');
        }

        require_once $taskFile;

        $this->printMessage('Task wurde erfolgreich ausgeführt.');
    }

    /* =========================
     * 🤖 AI BOT INFO + 🔬 Forschung
     * ========================= */
    function aiinfo()
    {
        $db = Database::get();
        global $LNG, $resource;

        $bots = $db->select(
            "SELECT
                u.id,
                u.username,
                u.bot_next_action,

                -- 🔬 Forschung Queue
                u.b_tech,
                u.b_tech_id,
                u.b_tech_planet,
                u.b_tech_queue,

                -- 🔬 Forschungslevel (NUR existierende Standard-Spalten)
                u.spy_tech,
                u.computer_tech,
                u.military_tech,
                u.defence_tech,
                u.shield_tech,
                u.energy_tech,
                u.hyperspace_tech,
                u.combustion_tech,
                u.impulse_motor_tech,
                u.hyperspace_motor_tech,
                u.laser_tech,
                u.ionic_tech,
                u.buster_tech,
                u.intergalactic_tech,
                u.expedition_tech,

                p.id AS planet_id,
                p.name AS planet_name,
                p.galaxy, p.system, p.planet,

                -- Ressourcen
                p.metal, p.crystal, p.deuterium,

                -- Minen
                p.metal_mine,
                p.crystal_mine,
                p.deuterium_sintetizer,

                -- Energie
                p.solar_plant,
                p.energy,
                p.energy_used,

                -- Infrastruktur
                p.metal_store,
                p.crystal_store,
                p.deuterium_store,
                p.robot_factory,
                p.hangar,
                p.laboratory,

                -- Verteidigung
                p.misil_launcher,
                p.small_laser,
                p.big_laser,
                p.gauss_canyon,
                p.ionic_canyon,
                p.buster_canyon,
                p.small_protection_shield,
                p.big_protection_shield,

                -- Bauqueue
                p.b_building

            FROM ".DB_PREFIX."users u
            INNER JOIN ".DB_PREFIX."planets p
              ON p.id_owner = u.id
             AND p.planet_type = 1
            WHERE u.is_bot = 1
            ORDER BY u.id ASC"
        );

        $now = time();

        foreach($bots as &$bot)
        {
            /* ============== ⚡ Energie & Bau ============== */
            $bot['energy_free'] = $bot['energy'] - $bot['energy_used'];

            $bot['build_status'] =
                ($bot['b_building'] > $now)
                ? 'läuft bis '.date('H:i:s', $bot['b_building'])
                : 'frei';

            $bot['next_action'] =
                $bot['bot_next_action']
                ? date('H:i:s', $bot['bot_next_action'])
                : '-';


            /* ============== 🔬 Forschungs-Levels sammeln ============== */

            $researchIds = [
                106,108,109,110,111,
                113,114,115,117,118,
                120,121,122,123,124
            ];

            $bot['research'] = [];

            foreach($researchIds as $rid)
            {
                if (!isset($resource[$rid])) {
                    continue;
                }

                $col = $resource[$rid];

                $bot['research'][$rid] = [
                    'name'  => $LNG['tech'][$rid] ?? ('Tech '.$rid),
                    'level' => (int)($bot[$col] ?? 0),
                ];
            }

            /* ============== 🟡 Laufende Forschung ============== */

            $bot['research_active'] = null;

            if (
                !empty($bot['b_tech'])
                && $bot['b_tech'] > $now
                && $bot['b_tech_id'] > 0
            ) {
                $tid = (int)$bot['b_tech_id'];

                $bot['research_active'] = [
                    'id'   => $tid,
                    'name' => $LNG['tech'][$tid] ?? ('Tech '.$tid),
                    'end'  => date('H:i:s', $bot['b_tech']),
                ];
            }
        }

        $this->tplObj->assign_vars([
            'bots' => $bots,
        ]);

        $this->display('page.bot.aiinfo.tpl');
    }
}

