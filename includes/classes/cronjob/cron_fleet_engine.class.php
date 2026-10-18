<?php
declare(strict_types=1);

class cron_fleet_engine
{
    public function run(): void
    {
        $token = getRandomString();
        $db    = Database::get();

        $db->update(
            "UPDATE %%FLEETS_EVENT%%
             SET `lock` = :token
             WHERE `lock` IS NULL
               AND `time` <= :time;",
            [
                ':time'  => TIMESTAMP,
                ':token' => $token,
            ]
        );

        if ($db->rowCount() === 0) {
            return;
        }

        require_once ROOT_PATH . 'includes/classes/class.FlyingFleetHandler.php';

        try {
            $fleetHandler = new FlyingFleetHandler();
            $fleetHandler->setToken($token);
            $fleetHandler->run();
        } finally {
            $db->update(
                "UPDATE %%FLEETS_EVENT%%
                 SET `lock` = NULL
                 WHERE `lock` = :token;",
                [
                    ':token' => $token,
                ]
            );
        }
    }
}
