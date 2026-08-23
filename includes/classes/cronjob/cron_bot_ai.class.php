<?php

class cron_bot_ai
{
    public function run(): void
    {
        require_once ROOT_PATH . 'includes/classes/aibot/BotAIManager.php';

        $db  = Database::get();
        $now = TIMESTAMP;

        $prefix = DB_PREFIX;
        if (substr($prefix, -1) !== '_') {
            $prefix .= '_';
        }

        $usersTable = $prefix . 'users';

        $bots = $db->select(
            "SELECT id, bot_next_spy, bot_next_expedition, bot_next_attack
             FROM {$usersTable}
             WHERE is_bot = 1"
        );

        foreach ($bots as $bot) {
            $spyReady    = empty($bot['bot_next_spy']) || (int)$bot['bot_next_spy'] <= $now;
            $expoReady   = empty($bot['bot_next_expedition']) || (int)$bot['bot_next_expedition'] <= $now;
            $attackReady = empty($bot['bot_next_attack']) || (int)$bot['bot_next_attack'] <= $now;

            if (!$spyReady && !$expoReady && !$attackReady) {
                continue;
            }

            usleep(random_int(80_000, 200_000));
            BotAIManager::run((int)$bot['id']);
        }
    }
}
