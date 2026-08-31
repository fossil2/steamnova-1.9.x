<?php
declare(strict_types=1);

require_once ROOT_PATH . 'includes/classes/aibot/BotAIManager.php';

/**
 * Kompatibilitäts-Wrapper.
 * Bestehende Stellen mit BotSpyAI::run() laufen weiter,
 * intern aber bereits über den neuen Manager.
 */
class BotSpyAI
{
    public static function run(int $botId): void
    {
        BotAIManager::run($botId);
    }
}
