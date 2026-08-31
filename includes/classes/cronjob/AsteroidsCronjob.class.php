<?php

require_once 'includes/classes/cronjob/CronjobTask.interface.php';

class AsteroidsCronjob implements CronjobTask
{
    public function run()
    {
        $langObjects = [];
        $db = Database::get();
        $config = Config::get(ROOT_UNI);
        $universe = ROOT_UNI;

        if ($config->asteroid_event < TIMESTAMP && (int) $config->asteroid_actif === 1) {
            $renewalTime = TIMESTAMP + 5 * 60;
            $asteroidCount = max(0, (int) $config->asteroid_count);

            $galaxyMin = 1;
            $systemMin = 1;
            $planetMin = 1;
            $galaxyMax = max(1, (int) $config->max_galaxy);
            $systemMax = max(1, (int) $config->max_system);
            $planetMax = max(1, (int) $config->max_planets);

            $db->update(
                "UPDATE %%CONFIG%% SET 
                    asteroid_event = :asteroid_event,
                    asteroid_round = asteroid_round + 1
                 WHERE uni = :uni;",
                [
                    ':asteroid_event' => $renewalTime,
                    ':uni' => $universe,
                ]
            );

            $db->delete(
                "DELETE FROM %%PLANETS%% WHERE image = :image AND universe = :universe;",
                [
                    ':image' => 'asteroid',
                    ':universe' => $universe,
                ]
            );

            $lastGalaxy = 0;
            $lastSystem = 0;
            $lastPlanet = 0;

            $asteroidMetal = (int) $config->asteroid_metal;
            $asteroidCrystal = (int) $config->asteroid_crystal;
            $asteroidDeuterium = (int) $config->asteroid_deuterium;
            $asteroidRound = (int) $config->asteroid_round;

            $spawned = 0;
            $attempts = 0;
            $maxAttempts = max(100, $asteroidCount * 20);

            while ($spawned < $asteroidCount && $attempts < $maxAttempts) {
                $attempts++;

                $gala = mt_rand($galaxyMin, $galaxyMax);
                $syst = mt_rand($systemMin, $systemMax);
                $plan = mt_rand($planetMin, $planetMax);

                $sqlVerify = "SELECT id FROM %%PLANETS%% 
                              WHERE galaxy = :galaxy 
                                AND system = :system 
                                AND planet = :planet 
                                AND universe = :universe
                              LIMIT 1;";

                $verify = $db->select($sqlVerify, [
                    ':galaxy' => $gala,
                    ':system' => $syst,
                    ':planet' => $plan,
                    ':universe' => $universe,
                ]);

                if ($db->rowCount($verify) > 0) {
                    continue;
                }

                $metalRand = $asteroidMetal + round($asteroidMetal / 100 * $asteroidRound);
                $crystalRand = $asteroidCrystal + round($asteroidCrystal / 100 * $asteroidRound);
                $deuteriumRand = $asteroidDeuterium + round($asteroidDeuterium / 100 * $asteroidRound);

                $db->insert(
                    "INSERT INTO %%PLANETS%% SET
                        name = :name,
                        id_owner = :id_owner,
                        universe = :universe,
                        galaxy = :galaxy,
                        system = :system,
                        planet = :planet,
                        planet_type = :planet_type,
                        image = :image,
                        diameter = :diameter,
                        metal = :metal,
                        crystal = :crystal,
                        deuterium = :deuterium,
                        last_update = :last_update;",
                    [
                        ':name' => 'Asteroid',
                        ':id_owner' => null,
                        ':universe' => $universe,
                        ':galaxy' => $gala,
                        ':system' => $syst,
                        ':planet' => $plan,
                        ':planet_type' => 1,
                        ':image' => 'asteroid',
                        ':diameter' => 9800,
                        ':metal' => $metalRand,
                        ':crystal' => $crystalRand,
                        ':deuterium' => $deuteriumRand,
                        ':last_update' => TIMESTAMP,
                    ]
                );

                $lastGalaxy = $gala;
                $lastSystem = $syst;
                $lastPlanet = $plan;
                $spawned++;
            }

            $sqlMessaging = "SELECT DISTINCT id, lang FROM %%USERS%%";
            $resultMessaging = $db->select($sqlMessaging);

            foreach ($resultMessaging as $userInfo) {
                if (!isset($langObjects[$userInfo['lang']])) {
                    $langObjects[$userInfo['lang']] = new Language($userInfo['lang']);
                    $langObjects[$userInfo['lang']]->includeData(['L18N', 'INGAME', 'TECH', 'CUSTOM']);
                }

                $LNG = $langObjects[$userInfo['lang']];
                $message = '<span class="admin">' . sprintf($LNG['custom_asteroid'], $lastGalaxy, $lastSystem, $lastPlanet) . '</span>';

                PlayerUtil::sendMessage(
                    $userInfo['id'],
                    0,
                    $LNG['cronjob_asteroid_msg_from'],
                    50,
                    $LNG['cronjob_asteroid_msg_to'],
                    $message,
                    TIMESTAMP
                );
            }
        } elseif ((int) $config->asteroid_actif === 0 && (int) $config->asteroid_event > 0) {
            $db->delete(
                "DELETE FROM %%PLANETS%% WHERE image = :image AND universe = :universe;",
                [
                    ':image' => 'asteroid',
                    ':universe' => $universe,
                ]
            );

            $db->update(
                "UPDATE %%CONFIG%% SET 
                    asteroid_event = :asteroid_event,
                    asteroid_round = :asteroid_round
                 WHERE uni = :uni;",
                [
                    ':asteroid_event' => 0,
                    ':asteroid_round' => 0,
                    ':uni' => $universe,
                ]
            );

            $sqlMessaging = "SELECT DISTINCT id, lang FROM %%USERS%%";
            $resultMessaging = $db->select($sqlMessaging);

            foreach ($resultMessaging as $userInfo) {
                if (!isset($langObjects[$userInfo['lang']])) {
                    $langObjects[$userInfo['lang']] = new Language($userInfo['lang']);
                    $langObjects[$userInfo['lang']]->includeData(['L18N', 'INGAME', 'TECH', 'CUSTOM']);
                }

                $LNG = $langObjects[$userInfo['lang']];
                $message = '<span class="admin">' . $LNG['cronjob_asteroid_msg_user_event_close'] . '</span>';

                PlayerUtil::sendMessage(
                    $userInfo['id'],
                    0,
                    $LNG['cronjob_asteroid_msg_from'],
                    50,
                    $LNG['cronjob_asteroid_msg_to'],
                    $message,
                    TIMESTAMP
                );
            }
        }
    }
}
