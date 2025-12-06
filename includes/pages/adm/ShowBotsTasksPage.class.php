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
            'attack_idlers', 'attack_players', 'autobuilder', 'autorefresher',
            'build_ships', 'colonize_planets', 'remove_messages', 'send_expedition', 'send_recyclers'
        ];

        if (!in_array($task, $allowedTasks)) {
            $this->printMessage('Ungültiger Task');
        }

        $taskFile = 'includes/bot_tasks/' . $task . '.php';

        if (!file_exists($taskFile)) {
            $this->printMessage('Task-Datei nicht gefunden: ' . htmlspecialchars($taskFile));
        }

        require_once $taskFile;

        $this->printMessage('Task "' . htmlspecialchars($task) . '" wurde erfolgreich ausgeführt.');
    }
}
