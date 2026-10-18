<?php

/**
 * @mods Quests
 * @version 1.0
 * @author Danter14
 * @licence MIT
 * @package 2Moons
 * @version 1.8 - 1.9 - 2.0
 */

class ShowQuestsAjaxPage extends AbstractGamePage
{
    public static $requireModule = 0;

    public function __construct()
    {
        parent::__construct();
        $this->setWindow('ajax');
    }


    /**
     * Automatische Quest-Texte in der aktuell
     * geladenen Sprache erzeugen.
     *
     * Alte Quests ohne AUTO: bleiben unverändert.
     */
    private function getQuestLanguageText(
        string $storedTitle,
        string $storedDescription,
        int $objectiveId,
        int $objectiveLevel
    ): array {
        global $LNG;

        /*
         * Alte Quest mit fest gespeichertem Titel.
         */
        if (strpos($storedTitle, 'AUTO:') !== 0) {
            return [
                'title'       => $storedTitle,
                'description' => $storedDescription,
            ];
        }

        $type = substr($storedTitle, 5);

        $allowedTypes = [
            'build',
            'research',
            'fleet',
            'defense',
            'officer',
        ];

        if (
            !in_array($type, $allowedTypes, true)
            || !isset($LNG['tech'][$objectiveId])
        ) {
            return [
                'title'       => $storedTitle,
                'description' => $storedDescription,
            ];
        }

        $objectName = $LNG['tech'][$objectiveId];

        switch ($type) {

            case 'build':

                $title = isset($LNG['quest_task_build'])
                    ? sprintf(
                        $LNG['quest_task_build'],
                        $objectName
                    )
                    : $objectName;

                $description = isset($LNG['quest_desc_build'])
                    ? sprintf(
                        $LNG['quest_desc_build'],
                        $objectName,
                        $objectiveLevel
                    )
                    : $objectName;

                break;


            case 'research':

                $title = isset($LNG['quest_task_research'])
                    ? sprintf(
                        $LNG['quest_task_research'],
                        $objectName
                    )
                    : $objectName;

                $description = isset($LNG['quest_desc_research'])
                    ? sprintf(
                        $LNG['quest_desc_research'],
                        $objectName,
                        $objectiveLevel
                    )
                    : $objectName;

                break;


            case 'fleet':

                $title = isset($LNG['quest_task_fleet'])
                    ? sprintf(
                        $LNG['quest_task_fleet'],
                        $objectName
                    )
                    : $objectName;

                $description = isset($LNG['quest_desc_fleet'])
                    ? sprintf(
                        $LNG['quest_desc_fleet'],
                        $objectiveLevel,
                        $objectName
                    )
                    : $objectName;

                break;


            case 'defense':

                $title = isset($LNG['quest_task_defense'])
                    ? sprintf(
                        $LNG['quest_task_defense'],
                        $objectName
                    )
                    : $objectName;

                $description = isset($LNG['quest_desc_defense'])
                    ? sprintf(
                        $LNG['quest_desc_defense'],
                        $objectiveLevel,
                        $objectName
                    )
                    : $objectName;

                break;


            case 'officer':

                $title = isset($LNG['quest_task_officer'])
                    ? sprintf(
                        $LNG['quest_task_officer'],
                        $objectName
                    )
                    : $objectName;

                $description = isset($LNG['quest_desc_officer'])
                    ? sprintf(
                        $LNG['quest_desc_officer'],
                        $objectName,
                        $objectiveLevel
                    )
                    : $objectName;

                break;


            default:

                $title = $storedTitle;
                $description = $storedDescription;

                break;
        }

        return [
            'title'       => $title,
            'description' => $description,
        ];
    }


    /**
     * Quest-Liste anzeigen.
     */
    public function show()
    {
        global $LNG, $USER;

        $db = Database::get();

        $categorie_id = isset($_GET['categorie_id'])
            ? (int) $_GET['categorie_id']
            : 1;

        if ($categorie_id <= 0) {
            $categorie_id = 1;
        }

        /*
         * Kategorien laden.
         */
        $result_cat = $db->select(
            "SELECT * FROM %%QUESTS_CATEGORIES%%;"
        );

        /*
         * Aktive Quests dieser Kategorie laden.
         */
        $sql = "SELECT ql.*, qu.*
FROM %%QUESTS_LISTS%% ql
LEFT JOIN %%QUESTS_USERS%% qu
    ON qu.user_questsID = ql.questsID
    AND qu.userId = :userID
WHERE ql.quest_actif = 1
    AND ql.questsCategories = :idCat
ORDER BY ql.questsID;";

        $result = $db->select($sql, [
            ':idCat'  => $categorie_id,
            ':userID' => $USER['id'],
        ]);

        $content_list = '';

        foreach ($result as $response) {

            /*
             * Event-Ende prüfen.
             */
            if (
                !empty($response['quest_time_finish_event'])
                && (int) $response['quest_time_finish_event'] > TIMESTAMP
            ) {

                $event_finish = _date(
                    $LNG['php_tdformat'],
                    $response['quest_time_finish_event'],
                    $USER['timezone']
                );

            } elseif (
                !empty($response['quest_time_finish_event'])
                && (int) $response['quest_time_finish_event'] <= TIMESTAMP
            ) {

                $event_finish = -1;

                $db->update(
                    "UPDATE %%QUESTS_LISTS%%
                     SET quest_actif = 0
                     WHERE questsID = :questsID;",
                    [
                        ':questsID' => $response['questsID'],
                    ]
                );

            } else {

                $event_finish = 0;
            }

            /*
             * Belohnungen.
             */
            $list_reward = [
                901 => $response['quest_metal_reward'],
                902 => $response['quest_crystal_reward'],
                903 => $response['quest_deuterium_reward'],
                921 => $response['quest_darkmatter_reward'],
            ];

            /*
             * Quest-Container.
             */
            $content_list .= "
                <div class='quest_list'
                     id='quest_" . (int) $response['questsID'] . "'>

                    <div class='label_list'>
                        <div>
            ";

            /*
             * Event-Badge.
             */
            if ((int) $response['quest_event'] === 1) {

                $content_list .= "
                    <span class='badge info quest_event_badge'>
                        " . $LNG['quest_event'] . "
                    </span>
                ";
            }

            /*
             * Quest-Status:
             *
             * NULL = Noch nicht angenommen
             * 1    = In Bearbeitung
             * 2    = Abgeschlossen
             */
            if (is_null($response['user_quest_users_finish'])) {

                $content_list .= "
                    <span
                        id='quest_badge_" . (int) $response['questsID'] . "'
                        class='badge danger'>
                        " . $LNG['quest_33'] . "
                    </span>
                ";

            } elseif ((int) $response['user_quest_users_finish'] === 1) {

                $content_list .= "
                    <span
                        id='quest_badge_" . (int) $response['questsID'] . "'
                        class='badge warning'>
                        " . $LNG['quest_40'] . "
                    </span>
                ";

            } else {

                $content_list .= "
                    <span
                        id='quest_badge_" . (int) $response['questsID'] . "'
                        class='badge success'>
                        " . $LNG['quest_38'] . "
                    </span>
                ";
            }

            $content_list .= "</div>";

            /*
             * Event-Ende anzeigen.
             */
            if ($event_finish !== 0 && $event_finish !== -1) {

                $content_list .= "
                    <span class='badge info'>
                        " . $LNG['quest_39'] . " " . $event_finish . "
                    </span>
                ";

            } elseif ($event_finish === -1) {

                $content_list .= "
                    <span class='badge warning'>
                        " . $LNG['quest_event_finished'] . "
                    </span>
                ";
            }


            /*
             * Quest-Titel und Beschreibung automatisch
             * in der aktuellen Sprache erzeugen.
             */
            $questText = $this->getQuestLanguageText(
                (string) $response['quest_title'],
                (string) $response['quest_description'],
                (int) $response['quest_objectif'],
                (int) $response['quest_objectif_level']
            );

            $questTitle = htmlspecialchars(
                $questText['title'],
                ENT_QUOTES,
                'UTF-8'
            );

            /*
             * Automatische Beschreibung ist reiner Text.
             * Alte Beschreibungen können <br> enthalten.
             */
            if (strpos((string) $response['quest_title'], 'AUTO:') === 0) {

                $questDescription = htmlspecialchars(
                    $questText['description'],
                    ENT_QUOTES,
                    'UTF-8'
                );

            } else {

                $questDescription =
                    $questText['description'];
            }


            $content_list .= "
                    </div>

                    <div class='title_quest'>
                        <h2>" . $questTitle . "</h2>
                    </div>

                    <div class='quest_description'>
                        " . $questDescription . "
                    </div>

                    <div class='reward'>

                        <div class='quest_rewards'>
                            <ul>
            ";

            /*
             * Ressourcen-Belohnungen.
             */
            foreach ($list_reward as $key => $reward) {

                if ((int) $reward <= 0) {
                    continue;
                }

                $resourceName = isset($LNG['tech'][$key])
                    ? $LNG['tech'][$key]
                    : (string) $key;

                $content_list .= "
                    <li>
                        <span class='quest_reward_amount'>
                            " . number_format((int) $reward) . "
                        </span>

                        <span class='quest_reward_name'>
                            " . $resourceName . "
                        </span>
                    </li>
                ";
            }


            /*
             * Quest-Ziel.
             */
            $questObjective =
                (int) $response['quest_objectif'];

            $objectiveName =
                isset($LNG['tech'][$questObjective])
                    ? $LNG['tech'][$questObjective]
                    : (string) $questObjective;


            $content_list .= "
                            </ul>
                        </div>

                        <div class='quest_objective'>

                            <span class='quest_objective_value'>
                                +" . number_format(
                                    (int) $response['quest_objectif_level']
                                ) . "
                            </span>

                            <span class='quest_objective_name'>
                                " . $objectiveName . "
                            </span>

                        </div>
            ";


            /*
             * Quest starten oder Fortschritt anzeigen.
             */
            if (is_null($response['user_quest_users_finish'])) {

                $content_list .= "
                    <div
                        class='quest_action'
                        id='quest_button_" . (int) $response['questsID'] . "'>

                        <button
                            type='button'
                            id='btn_quest_" . (int) $response['questsID'] . "'
                            onclick='questConfirm(" . (int) $response['questsID'] . ")'>

                            " . $LNG['quest_20'] . "

                        </button>

                    </div>
                ";

            } else {

                $content_list .= "
                    <div
                        class='progresse_bar_border'
                        id='quest_button_" . (int) $response['questsID'] . "'>
                    </div>
                ";
            }


            $content_list .= "
                    </div>
                </div>
            ";
        }


        /*
         * Keine Kategorien und keine Quests vorhanden.
         */
        if (!$result_cat && !$result) {

            $content_list .= "
                <div class='quest_list quest_empty'>
                    <h2>" . $LNG['quest_42'] . "</h2>
                </div>
            ";
        }


        /*
         * Kategorie vorhanden, aber keine Quest darin.
         */
        if ($result_cat && !$result) {

            $content_list .= "
                <div class='quest_list quest_empty'>
                    <h2>" . $LNG['quest_32'] . "</h2>
                </div>
            ";
        }


        echo json_encode(
            $content_list,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }


    /**
     * Zählt Schiffe in fliegenden Flotten.
     */
    public function fleetVol($fleetId): int
    {
        global $USER;

        $db = Database::get();

        $result = $db->select(
            "SELECT fleet_owner, fleet_array
             FROM %%FLEETS%%
             WHERE fleet_owner = :userID",
            [
                ':userID' => $USER['id'],
            ]
        );

        $fleet_count = 0;

        if (!$result) {
            return 0;
        }

        foreach ($result as $fleet) {

            $shipArray = array_filter(
                explode(';', $fleet['fleet_array'])
            );

            foreach ($shipArray as $ship) {

                $shipDetail = explode(',', $ship);

                if (count($shipDetail) < 2) {
                    continue;
                }

                $shipId =
                    (int) $shipDetail[0];

                $shipCount =
                    (int) $shipDetail[1];

                if ($shipId === (int) $fleetId) {
                    $fleet_count += $shipCount;
                }
            }
        }

        return $fleet_count;
    }


    /**
     * Quest annehmen / starten.
     */
    public function runQuest()
    {
        global $LNG, $USER, $resource;

        $db = Database::get();

        $quest_id = isset($_POST['quest_id'])
            ? (int) $_POST['quest_id']
            : 0;

        $response = [];


        /*
         * Quest laden.
         */
        $result = $db->selectSingle(
            "SELECT *
             FROM %%QUESTS_LISTS%%
             WHERE questsID = :idQuest;",
            [
                ':idQuest' => $quest_id,
            ]
        );


        /*
         * Quest existiert nicht.
         */
        if (empty($result)) {

            $response['error'] =
                'danger';

            $response['message'] =
                $LNG['quest_error_not_exists'];

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            return;
        }


        /*
         * Quest nicht aktiv.
         */
        if ((int) $result['quest_actif'] !== 1) {

            $response['error'] =
                'warning';

            $response['message'] =
                $LNG['quest_error_not_active'];

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            return;
        }


        /*
         * Event bereits beendet.
         */
        if (
            (int) $result['quest_event'] === 1
            && !empty($result['quest_time_finish_event'])
            && (int) $result['quest_time_finish_event'] < TIMESTAMP
        ) {

            $response['error'] =
                'warning';

            $response['message'] =
                $LNG['quest_34'];

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            return;
        }


        /*
         * Prüfen, ob Quest bereits angenommen wurde.
         */
        $result_control = $db->selectSingle(
            "SELECT *
             FROM %%QUESTS_USERS%%
             WHERE user_questsID = :questID
                AND userId = :userID;",
            [
                ':questID' => $quest_id,
                ':userID'  => $USER['id'],
            ]
        );


        if (!empty($result_control)) {

            $response['error'] =
                'danger';

            $response['message'] =
                $LNG['quest_error_already_accepted'];

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            return;
        }


        /*
         * Quest-Ziel prüfen.
         */
        $objectiveId =
            (int) $result['quest_objectif'];

        if (!isset($resource[$objectiveId])) {

            $response['error'] =
                'danger';

            $response['message'] =
                $LNG['quest_error_invalid_target'];

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            return;
        }


        /*
         * Standardmäßig Planetendaten.
         */
        $table =
            "%%PLANETS%% WHERE id_owner = :where";


        /*
         * Forschung / Benutzerwerte befinden sich in USERS.
         */
        if (
            ($objectiveId > 100 && $objectiveId < 200)
            ||
            ($objectiveId > 600 && $objectiveId < 700)
        ) {

            $table =
                "%%USERS%% WHERE id = :where";
        }


        $field =
            $resource[$objectiveId];


        /*
         * Aktuellen Stand beim Start speichern.
         */
        $result_quest_user = $db->selectSingle(
            "SELECT SUM($field) AS total
             FROM $table",
            [
                ':where' => $USER['id'],
            ]
        );


        $currentTotal =
            !empty($result_quest_user['total'])
                ? (int) $result_quest_user['total']
                : 0;


        /*
         * Schiffe in fliegenden Flotten mitzählen.
         */
        if ($objectiveId > 200 && $objectiveId < 300) {

            $currentTotal +=
                $this->fleetVol($objectiveId);
        }


        /*
         * Quest für Benutzer starten.
         */
        $db->insert(
            "INSERT INTO %%QUESTS_USERS%%
             SET
                userId = :userID,
                user_questsID = :questID,
                user_quest_objectif = :objectiveID,
                user_quest_objectif_level = :objectiveLevel,
                user_quest_objectif_level_user = :objectiveLevelUser,
                user_quest_users_accept = :createdAt,
                user_quest_users_finish = :questFinish;",
            [
                ':userID' =>
                    $USER['id'],

                ':questID' =>
                    $result['questsID'],

                ':objectiveID' =>
                    $objectiveId,

                ':objectiveLevel' =>
                    $result['quest_objectif_level'],

                ':objectiveLevelUser' =>
                    $currentTotal,

                ':createdAt' =>
                    TIMESTAMP,

                ':questFinish' =>
                    1,
            ]
        );


        /*
         * Titel für Rückmeldung ebenfalls übersetzen.
         */
        $questText = $this->getQuestLanguageText(
            (string) $result['quest_title'],
            (string) $result['quest_description'],
            $objectiveId,
            (int) $result['quest_objectif_level']
        );


        /*
         * Rückmeldung.
         */
        $response['error'] =
            'success';

        $response['message'] = sprintf(
            $LNG['quest_started'],
            $questText['title']
        );

        $response['questID'] =
            (int) $result['questsID'];

        $response['badgeStyle'] =
            'warning';

        $response['badgeContent'] =
            $LNG['quest_40'];


        echo json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }


    /**
     * Quest-Fortschritt prüfen.
     */
    public function verifQuest()
    {
        global $USER, $resource;

        $db = Database::get();

        $result = $db->select(
            "SELECT *
             FROM %%QUESTS_USERS%%
             WHERE userId = :userId;",
            [
                ':userId' => $USER['id'],
            ]
        );

        $responseQuest = [];


        foreach ($result as $verif) {

            /*
             * Bereits abgeschlossene Quests können
             * weiterhin angezeigt werden.
             */
            $objectiveId =
                (int) $verif['user_quest_objectif'];

            if (!isset($resource[$objectiveId])) {
                continue;
            }


            /*
             * Standardmäßig Planetendaten.
             */
            $table =
                "%%PLANETS%% WHERE id_owner = :where";


            /*
             * Forschung / Benutzerwerte.
             */
            if (
                ($objectiveId > 100 && $objectiveId < 200)
                ||
                ($objectiveId > 600 && $objectiveId < 700)
            ) {

                $table =
                    "%%USERS%% WHERE id = :where";
            }


            $field =
                $resource[$objectiveId];


            /*
             * Aktuellen Stand laden.
             */
            $verif_quest_user = $db->selectSingle(
                "SELECT SUM($field) AS total
                 FROM $table",
                [
                    ':where' => $USER['id'],
                ]
            );


            $currentTotal =
                !empty($verif_quest_user['total'])
                    ? (int) $verif_quest_user['total']
                    : 0;


            /*
             * Schiffe in fliegenden Flotten mitzählen.
             */
            if ($objectiveId > 200 && $objectiveId < 300) {

                $currentTotal +=
                    $this->fleetVol($objectiveId);
            }


            $startValue =
                (int) $verif['user_quest_objectif_level_user'];

            $requiredIncrease =
                (int) $verif['user_quest_objectif_level'];

            $targetValue =
                $startValue + $requiredIncrease;


            /*
             * Fortschritt berechnen.
             */
            $progressValue =
                $currentTotal - $startValue;

            if ($progressValue < 0) {
                $progressValue = 0;
            }


            if ($requiredIncrease <= 0) {

                $percent = 0;

            } else {

                $percent = round(
                    ($progressValue / $requiredIncrease) * 100,
                    2
                );
            }


            if ($percent < 0) {
                $percent = 0;
            }

            if ($percent > 100) {
                $percent = 100;
            }


            $responseQuest[
                $verif['user_questsID']
            ] = [

                'user_quest_finish' =>
                    $currentTotal >= $targetValue,

                'count_current_pourcent' =>
                    $percent,

                'user_quest_users_finish' =>
                    (int) $verif['user_quest_users_finish'],
            ];
        }


        echo json_encode(
            $responseQuest,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }


    /**
     * Quest abschließen und Belohnung geben.
     */
    public function finishQuest()
    {
        global $LNG, $USER, $resource;

        $db = Database::get();

        $quest_id = isset($_POST['quest_id'])
            ? (int) $_POST['quest_id']
            : 0;

        $response = [];


        /*
         * Benutzer-Quest laden.
         */
        $result = $db->selectSingle(
            "SELECT *
             FROM %%QUESTS_USERS%%
             WHERE user_questsID = :questsID
                AND userId = :userId;",
            [
                ':questsID' => $quest_id,
                ':userId'   => $USER['id'],
            ]
        );


        /*
         * Quest nicht gefunden.
         */
        if (empty($result)) {

            $response['error'] =
                'danger';

            $response['message'] =
                $LNG['quest_error_not_found'];

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            return;
        }


        /*
         * Bereits abgeschlossen.
         */
        if (
            (int) $result['user_quest_users_finish'] === 2
        ) {

            $response['error'] =
                'danger';

            $response['message'] =
                $LNG['quest_error_already_finished'];

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            return;
        }


        $objectiveId =
            (int) $result['user_quest_objectif'];


        /*
         * Quest-Ziel prüfen.
         */
        if (!isset($resource[$objectiveId])) {

            $response['error'] =
                'danger';

            $response['message'] =
                $LNG['quest_error_invalid_target'];

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            return;
        }


        /*
         * Standardmäßig Planetendaten.
         */
        $table =
            "%%PLANETS%% WHERE id_owner = :where";


        /*
         * Forschung / Benutzerwerte.
         */
        if (
            ($objectiveId > 100 && $objectiveId < 200)
            ||
            ($objectiveId > 600 && $objectiveId < 700)
        ) {

            $table =
                "%%USERS%% WHERE id = :where";
        }


        $field =
            $resource[$objectiveId];


        /*
         * Aktuellen Stand prüfen.
         */
        $result_quest_user = $db->selectSingle(
            "SELECT SUM($field) AS total
             FROM $table",
            [
                ':where' => $USER['id'],
            ]
        );


        $currentTotal =
            !empty($result_quest_user['total'])
                ? (int) $result_quest_user['total']
                : 0;


        /*
         * Schiffe in fliegenden Flotten ebenfalls mitzählen.
         */
        if ($objectiveId > 200 && $objectiveId < 300) {

            $currentTotal +=
                $this->fleetVol($objectiveId);
        }


        /*
         * Zielwert.
         */
        $targetValue =
            (int) $result['user_quest_objectif_level_user']
            + (int) $result['user_quest_objectif_level'];


        /*
         * Quest erfüllt.
         */
        if ($currentTotal >= $targetValue) {

            $this->rewardQuest(
                $result['user_questsID']
            );

            $response['questID'] =
                (int) $result['user_questsID'];

            $response['badgeStyle'] =
                'success';

            $response['badgeContent'] =
                $LNG['quest_38'];

            $response['error'] =
                'success';

            $response['message'] =
                $LNG['quest_finish_success'];

        } else {

            $response['error'] =
                'danger';

            $response['message'] =
                $LNG['quest_finish_invalid'];
        }


        echo json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }


    /**
     * Quest-Belohnung auszahlen.
     */
    public function rewardQuest($questID)
    {
        global $USER, $resource;

        $db = Database::get();


        /*
         * Quest-Belohnungen laden.
         */
        $result = $db->selectSingle(
            "SELECT
                quest_points_reward,
                quest_metal_reward,
                quest_crystal_reward,
                quest_deuterium_reward,
                quest_darkmatter_reward
             FROM %%QUESTS_LISTS%%
             WHERE questsID = :questID;",
            [
                ':questID' => $questID,
            ]
        );

        $response = [];

        if (empty($result)) {
            return $response;
        }


        /*
         * Planet-Ressourcen sammeln.
         */
        $field_planet = [];


        if (!empty($result['quest_metal_reward'])) {

            $metalReward =
                (int) $result['quest_metal_reward'];

            $response['metal'] =
                $metalReward;

            $field_planet[] =
                $resource[901]
                . ' = '
                . $resource[901]
                . ' + '
                . $metalReward;
        }


        if (!empty($result['quest_crystal_reward'])) {

            $crystalReward =
                (int) $result['quest_crystal_reward'];

            $response['crystal'] =
                $crystalReward;

            $field_planet[] =
                $resource[902]
                . ' = '
                . $resource[902]
                . ' + '
                . $crystalReward;
        }


        if (!empty($result['quest_deuterium_reward'])) {

            $deuteriumReward =
                (int) $result['quest_deuterium_reward'];

            $response['deuterium'] =
                $deuteriumReward;

            $field_planet[] =
                $resource[903]
                . ' = '
                . $resource[903]
                . ' + '
                . $deuteriumReward;
        }


        /*
         * Benutzerwerte vorbereiten.
         */
        $userUpdates = [];


        if (!empty($result['quest_darkmatter_reward'])) {

            $darkmatterReward =
                (int) $result['quest_darkmatter_reward'];

            $response['darkmatter'] =
                $darkmatterReward;

            $userUpdates[] =
                $resource[921]
                . ' = '
                . $resource[921]
                . ' + '
                . $darkmatterReward;
        }


        $questPointsReward =
            !empty($result['quest_points_reward'])
                ? (int) $result['quest_points_reward']
                : 0;


        $userUpdates[] =
            "reputation_quests =
             reputation_quests + :reputation_quests";


        /*
         * Benutzer aktualisieren.
         */
        $db->update(
            "UPDATE %%USERS%%
             SET " . implode(', ', $userUpdates) . "
             WHERE id = :userID;",
            [
                ':reputation_quests' =>
                    $questPointsReward,

                ':userID' =>
                    $USER['id'],
            ]
        );


        /*
         * Ressourcen auf dem aktuellen Planeten gutschreiben.
         */
        if (!empty($field_planet)) {

            $db->update(
                "UPDATE %%PLANETS%%
                 SET " . implode(', ', $field_planet) . "
                 WHERE id = :idPlanet;",
                [
                    ':idPlanet' =>
                        $USER['id_planet'],
                ]
            );
        }


        /*
         * Quest als abgeschlossen markieren.
         */
        $db->update(
            "UPDATE %%QUESTS_USERS%%
             SET user_quest_users_finish = :finish
             WHERE user_questsID = :questsID
                AND userId = :userId;",
            [
                ':finish' =>
                    2,

                ':questsID' =>
                    $questID,

                ':userId' =>
                    $USER['id'],
            ]
        );


        return $response;
    }
}