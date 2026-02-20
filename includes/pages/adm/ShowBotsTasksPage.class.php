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

$rows = $db->select(
    "SELECT
        u.id,
        u.username,
        u.bot_next_action,

        -- Forschung Queue
        u.b_tech,
        u.b_tech_id,
        u.b_tech_planet,
        u.b_tech_queue,

        -- Forschungslevel
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

        -- Planet
        p.id AS planet_id,
        p.name AS planet_name,
        p.galaxy, p.system, p.planet,

        p.metal,
        p.crystal,
        p.deuterium,

        p.metal_mine,
        p.crystal_mine,
        p.deuterium_sintetizer,
        p.solar_plant,
        p.energy,
        p.energy_used,

        p.robot_factory,
        p.hangar,
        p.laboratory,

        p.metal_store,
        p.crystal_store,
        p.deuterium_store,

        p.misil_launcher,
        p.small_laser,
        p.big_laser,
        p.gauss_canyon,
        p.ionic_canyon,
        p.buster_canyon,
        p.small_protection_shield,
        p.big_protection_shield,

        p.b_building

    FROM ".DB_PREFIX."users u
    LEFT JOIN ".DB_PREFIX."planets p
        ON p.id_owner = u.id
       AND p.planet_type = 1
    WHERE u.is_bot = 1
    ORDER BY u.id ASC"
);

    $now = time();
    $bots = [];

    foreach ($rows as $row)
    {
        $uid = $row['id'];

        /* ===== Bot einmal anlegen ===== */
        if (!isset($bots[$uid]))
        {
            $bots[$uid] = $row;
            $bots[$uid]['planets'] = [];
            $bots[$uid]['next_action'] = !empty($row['bot_next_action'])
    ? date('H:i:s', $row['bot_next_action'])
    : '-';

            /* ===== Forschung bauen ===== */
            $researchIds = [
                106,108,109,110,111,
                113,114,115,117,118,
                120,121,122,123,124
            ];

            $bots[$uid]['research'] = [];

            foreach ($researchIds as $rid)
            {
                if (!isset($resource[$rid])) continue;

                $col = $resource[$rid];

                $bots[$uid]['research'][$rid] = [
                    'name'  => $LNG['tech'][$rid] ?? ('Tech '.$rid),
                    'level' => (int)($row[$col] ?? 0),
                ];
            }

            /* ===== Aktive Forschung ===== */
            $bots[$uid]['research_active'] = null;

            if (!empty($row['b_tech']) && $row['b_tech'] > $now && $row['b_tech_id'] > 0)
            {
                $tid = (int)$row['b_tech_id'];

                $bots[$uid]['research_active'] = [
                    'id'   => $tid,
                    'name' => $LNG['tech'][$tid] ?? ('Tech '.$tid),
                    'end'  => date('H:i:s', $row['b_tech']),
                ];
            }
        }

        /* ===== Planeten anhängen ===== */
        if (!empty($row['planet_id']))
        {
 $bots[$uid]['planets'][] = [
    'planet_id' => $row['planet_id'],
    'name'      => $row['planet_name'],
    'coords'    => $row['galaxy'].':'.$row['system'].':'.$row['planet'],

    'metal'     => $row['metal'],
    'crystal'   => $row['crystal'],
    'deuterium' => $row['deuterium'],

    'metal_mine' => $row['metal_mine'],
    'crystal_mine' => $row['crystal_mine'],
    'deut_synth' => $row['deuterium_sintetizer'],

    'solar'  => $row['solar_plant'],
    'energy' => $row['energy'] - $row['energy_used'],

    'robot' => $row['robot_factory'],
    'hangar' => $row['hangar'],
    'lab' => $row['laboratory'],
    
    'metal_store' => $row['metal_store'],
'crystal_store' => $row['crystal_store'],
'deut_store' => $row['deuterium_store'],

'ml' => $row['misil_launcher'],
'sl' => $row['small_laser'],
'bl' => $row['big_laser'],
'ga' => $row['gauss_canyon'],
'io' => $row['ionic_canyon'],
'pl' => $row['buster_canyon'],
'ss' => $row['small_protection_shield'],
'bs' => $row['big_protection_shield'],

    'build_end' => $row['b_building'],
    'build_status' => ($row['b_building'] > $now)
        ? 'läuft bis '.date('H:i:s', $row['b_building'])
        : 'frei'
];
        }
    }

    $this->tplObj->assign_vars([
        'bots' => array_values($bots),
    ]);

    $this->display('page.bot.aiinfo.tpl');
}

}