<?php

class ShowBotsTasksPage extends AbstractAdminPage
{
    function __construct()
    {
        parent::__construct();
    }

  function show()
{
    $mode = HTTP::_GP('mode', '', true);

    switch ($mode) {
        case 'aiinfo':
            $this->aiinfo();
            return;
            
          

        case 'runAll':
            $this->runAllBots();
            return;

        case 'runAttack':
            $this->runAttackBots();
            return;

        case 'runSpy':
            $this->runSpyBots();
            return;

        case 'runExpedition':
            $this->runExpeditionBots();
            return;

        case 'runBotAttack':
            $this->runSingleBot('attack');
            return;

        case 'runBotSpy':
            $this->runSingleBot('spy');
            return;

        case 'runBotExpedition':
            $this->runSingleBot('expedition');
            return;

        case 'runBotAll':
            $this->runSingleBot('all');
            return;

        case 'logAttack':
            $this->showLog('bot_attack.log', 'Attack Log');
            return;

        case 'logSpy':
            $this->showLog('bot_spy.log', 'Spy Log');
            return;

        case 'logExpedition':
            $this->showLog('bot_expedition.log', 'Expedition Log');
            return;
            
            case 'toggleSpy':
              $this->toggleBotModule('spy');
              return;

         case 'toggleExpedition':
             $this->toggleBotModule('expedition');
             return;

         case 'toggleAttack':
             $this->toggleBotModule('attack');
             return;

        default:
            $this->display('page.bots.tasks.tpl');
            return;
    }
}
    
  private function runSingleBot(string $type)
{
    $botId = (int) HTTP::_GP('id', 0);

    if ($botId <= 0) {
        $this->printMessage('Ungültige Bot ID');
    }

    $db = Database::get();

    $bot = $db->selectSingle(
        "SELECT * FROM %%USERS%% WHERE id = :id AND is_bot = 1",
        [':id' => $botId]
    );

    if (!$bot) {
        $this->printMessage('Bot nicht gefunden');
    }

    require_once ROOT_PATH . 'includes/classes/aibot/BotAIAttack.php';
    require_once ROOT_PATH . 'includes/classes/aibot/BotAISpy.php';
    require_once ROOT_PATH . 'includes/classes/aibot/BotAIExpedition.php';
    require_once ROOT_PATH . 'includes/classes/aibot/BotAIManager.php';

    $runs = 0;

    if ($type === 'all') {
        BotAIManager::run($botId);
        $runs = 1;
    } else {
        $planets = $db->select(
            "SELECT * FROM %%PLANETS%% WHERE id_owner = :id AND planet_type = 1",
            [':id' => $botId]
        );

        foreach ($planets as $planet) {
            switch ($type) {
                case 'attack':
                    if (BotAIAttack::runPlanet($bot, $planet)) {
                        $runs++;
                    }
                    break;

                case 'spy':
                    if (BotAISpy::runPlanet($bot, $planet)) {
                        $runs++;
                    }
                    break;

                case 'expedition':
                    if (BotAIExpedition::runPlanet($bot, $planet)) {
                        $runs++;
                    }
                    break;
            }
        }
    }

    $this->printMessage(
        'Bot ' . $bot['username'] . ' ausgeführt (' . $type . ') | Aktionen: ' . $runs,
        true,
        ['game.php?page=botsTasks&mode=aiinfo']
    );
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

    private function getBots(): array
    {
        return Database::get()->select(
            "SELECT * FROM %%USERS%% WHERE is_bot = 1 ORDER BY id ASC;"
        );
    }

    private function getBotPlanets(int $userId): array
    {
        return Database::get()->select(
            "SELECT * 
             FROM %%PLANETS%% 
             WHERE id_owner = :uid
               AND planet_type = 1
             ORDER BY galaxy ASC, system ASC, planet ASC;",
            [
                ':uid' => $userId
            ]
        );
    }

    function runAllBots()
    {
        require_once ROOT_PATH . 'includes/classes/aibot/BotAIManager.php';

        $bots = $this->getBots();
        $count = 0;

        foreach ($bots as $bot) {
            BotAIManager::run((int)$bot['id']);
            $count++;
        }

        $this->printMessage('Alle Bots ausgeführt. Anzahl: ' . $count, true, ['game.php?page=botsTasks']);
    }

    function runAttackBots()
    {
        require_once ROOT_PATH . 'includes/classes/aibot/BotAIAttack.php';

        $bots = $this->getBots();
        $countBots = 0;
        $countRuns = 0;

        foreach ($bots as $bot) {
            $planets = $this->getBotPlanets((int)$bot['id']);
            $countBots++;

            foreach ($planets as $planet) {
                if (BotAIAttack::runPlanet($bot, $planet)) {
                    $countRuns++;
                }
            }
        }

        $this->printMessage(
            'Attack Bots ausgeführt. Bots: ' . $countBots . ' | erfolgreiche Angriffe: ' . $countRuns,
            true,
            ['game.php?page=botsTasks']
        );
    }

    function runSpyBots()
    {
        require_once ROOT_PATH . 'includes/classes/aibot/BotAISpy.php';

        $bots = $this->getBots();
        $countBots = 0;
        $countRuns = 0;

        foreach ($bots as $bot) {
            $planets = $this->getBotPlanets((int)$bot['id']);
            $countBots++;

            foreach ($planets as $planet) {
                if (BotAISpy::runPlanet($bot, $planet)) {
                    $countRuns++;
                }
            }
        }

        $this->printMessage(
            'Spy Bots ausgeführt. Bots: ' . $countBots . ' | erfolgreiche Aktionen: ' . $countRuns,
            true,
            ['game.php?page=botsTasks']
        );
    }

    function runExpeditionBots()
    {
        require_once ROOT_PATH . 'includes/classes/aibot/BotAIExpedition.php';

        $bots = $this->getBots();
        $countBots = 0;
        $countRuns = 0;

        foreach ($bots as $bot) {
            $planets = $this->getBotPlanets((int)$bot['id']);
            $countBots++;

            foreach ($planets as $planet) {
                if (BotAIExpedition::runPlanet($bot, $planet)) {
                    $countRuns++;
                }
            }
        }

        $this->printMessage(
            'Expedition Bots ausgeführt. Bots: ' . $countBots . ' | erfolgreiche Aktionen: ' . $countRuns,
            true,
            ['game.php?page=botsTasks']
        );
    }

    function showLog(string $file, string $title = 'Bot Log')
    {
        $path = ROOT_PATH . 'includes/ai_log/' . $file;

        if (!file_exists($path)) {
            $this->printMessage('Log Datei nicht gefunden: ' . $file, true, ['game.php?page=botsTasks']);
            return;
        }

        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!is_array($lines)) {
            $this->printMessage('Log Datei konnte nicht gelesen werden: ' . $file, true, ['game.php?page=botsTasks']);
            return;
        }

        $lines = array_slice($lines, -200);

        echo '<html><head><title>' . htmlspecialchars($title) . '</title></head><body>';
        echo '<h2>' . htmlspecialchars($title) . '</h2>';
        echo '<p><a href="game.php?page=botsTasks">Zurück</a></p>';
        echo '<pre style="font-size:12px; line-height:1.4; white-space:pre-wrap;">';

        foreach ($lines as $line) {
            echo htmlspecialchars($line) . "\n";
        }

        echo '</pre>';
        echo '</body></html>';
        exit;
    }
    
    private function toggleBotModule(string $module)
{
    $botId = (int) HTTP::_GP('id', 0);

    if ($botId <= 0) {
        $this->printMessage('Ungültige Bot ID');
    }

    $map = [
        'spy'        => 'bot_enable_spy',
        'expedition' => 'bot_enable_expedition',
        'attack'     => 'bot_enable_attack',
    ];

    if (!isset($map[$module])) {
        $this->printMessage('Ungültiges Modul');
    }

    $column = $map[$module];
    $db = Database::get();

    $bot = $db->selectSingle(
        "SELECT id, username, {$column} AS current_value
         FROM %%USERS%%
         WHERE id = :id AND is_bot = 1",
        [':id' => $botId]
    );

    if (!$bot) {
        $this->printMessage('Bot nicht gefunden');
    }

    $newValue = ((int)$bot['current_value'] === 1) ? 0 : 1;

    $db->update(
        "UPDATE %%USERS%% SET {$column} = :value WHERE id = :id",
        [
            ':value' => $newValue,
            ':id'    => $botId,
        ]
    );

    $state = $newValue === 1 ? 'aktiv' : 'deaktiviert';

    $this->printMessage(
        'Bot ' . $bot['username'] . ': ' . $module . ' ist jetzt ' . $state . '.',
        true,
        ['game.php?page=botsTasks&mode=aiinfo']
    );
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
                u.bot_next_spy,
                u.bot_next_expedition,
                u.bot_next_attack,
                u.bot_enable_spy,
                u.bot_enable_expedition,
                u.bot_enable_attack,

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

            if (!isset($bots[$uid]))
            {
                $bots[$uid] = $row;
                $bots[$uid]['planets'] = [];
                $bots[$uid]['next_action'] = !empty($row['bot_next_action']) ? date('H:i:s', $row['bot_next_action']) : '-';
                $bots[$uid]['next_spy'] = !empty($row['bot_next_spy']) ? date('H:i:s', $row['bot_next_spy']) : '-';
                $bots[$uid]['next_expedition'] = !empty($row['bot_next_expedition']) ? date('H:i:s', $row['bot_next_expedition']) : '-';
                $bots[$uid]['next_attack'] = !empty($row['bot_next_attack']) ? date('H:i:s', $row['bot_next_attack']) : '-';

                $researchIds = [
                    106,108,109,110,111,
                    113,114,115,117,118,
                    120,121,122,123,124
                ];

                $bots[$uid]['research'] = [];

                foreach ($researchIds as $rid)
                {
                    if (!isset($resource[$rid])) {
                        continue;
                    }

                    $col = $resource[$rid];

                    $bots[$uid]['research'][$rid] = [
                        'name'  => $LNG['tech'][$rid] ?? ('Tech '.$rid),
                        'level' => (int)($row[$col] ?? 0),
                    ];
                }

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

            if (!empty($row['planet_id']))
            {
                $bots[$uid]['planets'][] = [
                    'planet_id' => $row['planet_id'],
                    'name'      => $row['planet_name'],
                    'coords'    => $row['galaxy'].':'.$row['system'].':'.$row['planet'],

                    'metal'     => $row['metal'],
                    'crystal'   => $row['crystal'],
                    'deuterium' => $row['deuterium'],

                    'metal_mine'   => $row['metal_mine'],
                    'crystal_mine' => $row['crystal_mine'],
                    'deut_synth'   => $row['deuterium_sintetizer'],

                    'solar'  => $row['solar_plant'],
                    'energy' => $row['energy'] - $row['energy_used'],

                    'robot'  => $row['robot_factory'],
                    'hangar' => $row['hangar'],
                    'lab'    => $row['laboratory'],

                    'metal_store'   => $row['metal_store'],
                    'crystal_store' => $row['crystal_store'],
                    'deut_store'    => $row['deuterium_store'],

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