<?php

class ShowBotsTasksPage extends AbstractAdminPage
{
    function __construct()
    {
        parent::__construct();
    }

    /* =========================
     * Standard-Seite
     * ========================= */
    function show()
    {
        $this->display('page.bots.tasks.tpl');
    }

    /* =========================
     * Task ausführen
     * ========================= */
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
     * 🤖 AI BOT INFO (HIER!)
     * ========================= */
    function aiinfo()
    {
        $db = Database::get();

        $bots = $db->select(
            "SELECT
                u.id,
                u.username,
                u.bot_next_action,

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

                -- Baustatus
                p.b_building

            FROM " . DB_PREFIX . "users u
            INNER JOIN " . DB_PREFIX . "planets p
                ON p.id_owner = u.id
               AND p.planet_type = 1
            WHERE u.is_bot = 1
            ORDER BY u.id ASC"
        );

        $now = time();

        foreach ($bots as &$bot) {
            $bot['energy_free'] = $bot['energy'] - $bot['energy_used'];
            $bot['build_status'] = ($bot['b_building'] > $now)
                ? 'läuft bis ' . date('H:i:s', $bot['b_building'])
                : 'frei';

            $bot['next_action'] = $bot['bot_next_action']
                ? date('H:i:s', $bot['bot_next_action'])
                : '-';
        }

        $this->tplObj->assign_vars([
            'bots' => $bots,
        ]);

        $this->display('page.bot.aiinfo.tpl');
    }
}
