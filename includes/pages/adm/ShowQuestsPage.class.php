<?php

/**
 * @mods Quests
 * @version 1.0
 * @author Danter14
 * @licence MIT
 * @package 2Moons
 * @version 1.8 - 1.9 - 2.0
 */

class ShowQuestsPage extends AbstractAdminPage
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        global $LNG;

        $db = Database::get();

        $action = HTTP::_GP("action", "");

        /*
         * Kategorien
         */
        $result_list_cat = $this->listCategorie($db);

        if ($action == "add_categorie") {
            $this->addCategories($db, $_POST);
            die();
        }

        if ($action == "modal_categorie") {
            $this->modalCategorie($db, $_GET);
            die();
        }

        if ($action == "edit_categorie") {
            $this->editCategories($db, $_POST);
            die();
        }

        if ($action == "delete_categorie") {
            $this->deleteCategories($db, $_POST);
            die();
        }

        /*
         * Quests
         */
        $result_list_quests = $this->listQuest($db);

        if ($action == "add_quest") {
            $this->addQuest($db, $_POST);
            die();
        }

        if ($action == "modal_quest") {
            $this->modalQuest($db, $_GET);
            die();
        }

        if ($action == "edit_quest") {
            $this->editQuest($db, $_POST);
            die();
        }

        if ($action == "delete_quest") {
            $this->deleteQuest($db, $_POST);
            die();
        }

        $this->assign([
            "list_categories"    => $result_list_cat,
            "result_list_quests" => $result_list_quests,
            "objectifs_list"     => $this->objectifQuestLists(),
        ]);

        $this->display('QuestsPage.tpl');
    }


    /**
     * Kategorien auflisten
     */
    public function listCategorie($db)
    {
        global $LNG;

        $list_categories = [];

        $sql = "SELECT * FROM %%QUESTS_CATEGORIES%% ORDER BY questsCategories;";
        $result = $db->select($sql);

        foreach ($result as $response) {

            $categoryId = (int) $response["questsCategories"];
            $langKey = "quest_categorie_" . $categoryId;

            $categoryName = isset($LNG[$langKey])
                ? $LNG[$langKey]
                : "Kategorie " . $categoryId;

            $list_categories[] = [
                'id'          => $response["questsCategoriesID"],
                'categorieID' => $categoryId,
                'name'        => $categoryName,
            ];
        }

        return json_encode(
            $list_categories,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }


    /**
     * Kategorie hinzufügen
     */
    public function addCategories($db, $data)
    {
        global $LNG;

        $response = [];

        $categorieAdd = isset($data['categorie_add'])
            ? (int) $data['categorie_add']
            : 0;

        if ($categorieAdd <= 0) {

            $response["alert"] = "danger";
            $response["message"] = "Bitte eine gültige Kategorie-ID eingeben.";

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
            exit;
        }

        $sql = "SELECT questsCategories
                FROM %%QUESTS_CATEGORIES%%
                WHERE questsCategories = :questsCategories;";

        $result = $db->selectSingle(
            $sql,
            [
                ":questsCategories" => $categorieAdd
            ]
        );

        if (!empty($result)) {

            $response["alert"] = "danger";

            $response["message"] = isset($LNG["quest_37"])
                ? $LNG["quest_37"]
                : "Diese Kategorie existiert bereits.";

        } else {

            $sqlInsert = "INSERT INTO %%QUESTS_CATEGORIES%%
                          SET questsCategories = :questsCategories;";

            $db->insert(
                $sqlInsert,
                [
                    ":questsCategories" => $categorieAdd
                ]
            );

            $sql = "SELECT *
                    FROM %%QUESTS_CATEGORIES%%
                    WHERE questsCategories = :questsCategories;";

            $result = $db->selectSingle(
                $sql,
                [
                    ":questsCategories" => $categorieAdd
                ]
            );

            $langKey = "quest_categorie_" . $categorieAdd;

            $categoryName = isset($LNG[$langKey])
                ? $LNG[$langKey]
                : "Kategorie " . $categorieAdd;

            $response["alert"] = "success";
            $response["message"] = "Kategorie wurde erfolgreich erstellt.";

            $response["content"] =
                "<tr id='cat_" . $result['questsCategoriesID'] .
                "' class='cat" . $result['questsCategories'] . "'>" .

                "<td>" .
                $result['questsCategories'] .
                "</td>" .

                "<td>" .
                htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') .
                "</td>" .

                "<td>" .

                "<a style='cursor:pointer;' " .
                "onclick='javascript:modalCategorie(" .
                (int) $result['questsCategoriesID'] .
                ")'>" .

                "<i class='fa-solid fa-pen' " .
                "style='color:#237e23; padding-right:20px;'></i>" .

                "</a>" .

                "<a style='cursor:pointer;' " .
                "onclick='javascript:deleteCategorie(" .
                (int) $result['questsCategoriesID'] .
                ")'>" .

                "<i class='fa-solid fa-trash' " .
                "style='color:#c23934;'></i>" .

                "</a>" .

                "</td>" .

                "</tr>";
        }

        echo json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        exit;
    }


    /**
     * Kategorie für Modal laden
     */
    public function modalCategorie($db, $data)
    {
        global $LNG;

        $response = [];

        $idCat = isset($data['idCat'])
            ? (int) $data['idCat']
            : 0;

        if ($idCat <= 0) {

            $response["alert"] = "danger";
            $response["message"] = "Diese Kategorie existiert nicht.";

        } else {

            $sql = "SELECT *
                    FROM %%QUESTS_CATEGORIES%%
                    WHERE questsCategoriesID = :questsCategoriesID;";

            $result = $db->selectSingle(
                $sql,
                [
                    ":questsCategoriesID" => $idCat
                ]
            );

            if (!empty($result)) {

                $categoryId = (int) $result['questsCategories'];
                $langKey = "quest_categorie_" . $categoryId;

                $response["id"] = $result['questsCategoriesID'];
                $response["catId"] = $categoryId;

                $response["name"] = isset($LNG[$langKey])
                    ? $LNG[$langKey]
                    : "Kategorie " . $categoryId;

            } else {

                $response["alert"] = "danger";
                $response["message"] = "Diese Kategorie existiert nicht.";
            }
        }

        echo json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        exit;
    }


    /**
     * Kategorie bearbeiten
     */
    public function editCategories($db, $data)
    {
        global $LNG;

        $response = [];

        $id = isset($data['id'])
            ? (int) $data['id']
            : 0;

        $catId = isset($data['catId'])
            ? (int) $data['catId']
            : 0;

        if ($id <= 0 || $catId <= 0) {

            $response["alert"] = "danger";
            $response["message"] = "Bitte eine gültige Kategorie-ID eingeben.";

        } else {

            $sql = "SELECT questsCategoriesID
                    FROM %%QUESTS_CATEGORIES%%
                    WHERE questsCategories = :questsCategories
                    AND questsCategoriesID != :currentID;";

            $result = $db->selectSingle(
                $sql,
                [
                    ":questsCategories" => $catId,
                    ":currentID"        => $id,
                ]
            );

            if (!empty($result)) {

                $response["alert"] = "danger";

                $response["message"] = isset($LNG['quest_37'])
                    ? $LNG['quest_37']
                    : "Diese Kategorie existiert bereits.";

            } else {

                $sqlUpdate = "UPDATE %%QUESTS_CATEGORIES%%
                              SET questsCategories = :questsCategories
                              WHERE questsCategoriesID = :questsCategoriesID;";

                $db->update(
                    $sqlUpdate,
                    [
                        ":questsCategories"   => $catId,
                        ":questsCategoriesID" => $id,
                    ]
                );

                $response["alert"] = "success";
                $response["message"] = "Kategorie wurde erfolgreich aktualisiert.";
            }
        }

        echo json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        exit;
    }


    /**
     * Kategorie löschen
     */
    public function deleteCategories($db, $data)
    {
        $response = [];

        $id = isset($data['id'])
            ? (int) $data['id']
            : 0;

        if ($id <= 0) {

            $response["alert"] = "danger";
            $response["message"] = "Diese Kategorie existiert nicht.";

        } else {

            $sql = "SELECT questsCategoriesID, questsCategories
                    FROM %%QUESTS_CATEGORIES%%
                    WHERE questsCategoriesID = :idCat;";

            $result = $db->selectSingle(
                $sql,
                [
                    ":idCat" => $id
                ]
            );

            if (empty($result)) {

                $response["alert"] = "danger";
                $response["message"] = "Diese Kategorie existiert nicht.";

            } else {

                $categoryNumber = (int) $result['questsCategories'];

                $sqlDelete = "DELETE FROM %%QUESTS_CATEGORIES%%
                              WHERE questsCategoriesID = :idCat;";

                $db->delete(
                    $sqlDelete,
                    [
                        ":idCat" => $id
                    ]
                );

                /*
                 * Wichtig:
                 * In QUESTS_LISTS wird die Kategorie-Nummer gespeichert,
                 * nicht questsCategoriesID.
                 */
                $sqlQuests = "UPDATE %%QUESTS_LISTS%%
                              SET questsCategories = 0
                              WHERE questsCategories = :categoryNumber;";

                $db->update(
                    $sqlQuests,
                    [
                        ":categoryNumber" => $categoryNumber
                    ]
                );

                $response["alert"] = "success";
                $response["message"] = "Kategorie wurde erfolgreich gelöscht.";
            }
        }

        echo json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        exit;
    }


    /**
     * Quests auflisten
     */
    public function listQuest($db)
    {
        global $LNG, $USER;

        $listQuests = [];

        $sql = "SELECT *
                FROM %%QUESTS_LISTS%%
                ORDER BY questsID;";

        $result = $db->select($sql);

        foreach ($result as $response) {

            if ((int) $response['quest_time_finish_event'] > TIMESTAMP) {

                $eventFinish = _date(
                    $LNG['php_tdformat'],
                    $response['quest_time_finish_event'],
                    $USER['timezone']
                );

            } elseif (
                !empty($response['quest_time_finish_event']) &&
                (int) $response['quest_time_finish_event'] <= TIMESTAMP
            ) {

                $eventFinish = -1;

            } else {

                $eventFinish = 0;
            }

            $objectiveId =
                (int) $response['quest_objectif'];

            $objectiveLevel =
                (int) $response['quest_objectif_level'];

            $questText = $this->getQuestLanguageText(
                (string) $response['quest_title'],
                (string) $response['quest_description'],
                $objectiveId,
                $objectiveLevel
            );

            $listQuests[] = [
                'questsID' =>
                    $response['questsID'],

                'questsCategories' =>
                    $response['questsCategories'],

                'quest_title' =>
                    $questText['title'],

                'quest_description' =>
                    $questText['description'],

                'quest_objectif' =>
                    $objectiveId,

                'quest_objectif_level' =>
                    $objectiveLevel,

                'quest_points_reward' =>
                    $response['quest_points_reward'],

                'quest_metal_reward' =>
                    $response['quest_metal_reward'],

                'quest_crystal_reward' =>
                    $response['quest_crystal_reward'],

                'quest_deuterium_reward' =>
                    $response['quest_deuterium_reward'],

                'quest_darkmatter_reward' =>
                    $response['quest_darkmatter_reward'],

                'quest_actif' =>
                    $response['quest_actif'],

                'quest_created' =>
                    _date(
                        $LNG['php_tdformat'],
                        $response['quest_created'],
                        $USER['timezone']
                    ),

                'quest_event' =>
                    $response['quest_event'],

                'quest_time_finish_event' =>
                    $eventFinish,
            ];
        }

        return json_encode(
            $listQuests,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }


    /**
     * Liste aller möglichen Quest-Ziele.
     */
    public function objectifQuestLists()
    {
        global $LNG, $reslist;

        $objectifs = [];

        foreach ($reslist['build'] as $ID) {

            if (!isset($LNG['tech'][$ID])) {
                continue;
            }

            $objectifs[$ID] = [
                'id'   => $ID,
                'name' => $LNG['tech'][$ID],
                'type' => 'build',
            ];
        }


        foreach ($reslist['tech'] as $ID) {

            if (!isset($LNG['tech'][$ID])) {
                continue;
            }

            $objectifs[$ID] = [
                'id'   => $ID,
                'name' => $LNG['tech'][$ID],
                'type' => 'research',
            ];
        }


        foreach ($reslist['fleet'] as $ID) {

            if (!isset($LNG['tech'][$ID])) {
                continue;
            }

            $objectifs[$ID] = [
                'id'   => $ID,
                'name' => $LNG['tech'][$ID],
                'type' => 'fleet',
            ];
        }


        foreach ($reslist['defense'] as $ID) {

            if (!isset($LNG['tech'][$ID])) {
                continue;
            }

            $objectifs[$ID] = [
                'id'   => $ID,
                'name' => $LNG['tech'][$ID],
                'type' => 'defense',
            ];
        }


        foreach ($reslist['officier'] as $ID) {

            if (!isset($LNG['tech'][$ID])) {
                continue;
            }

            $objectifs[$ID] = [
                'id'   => $ID,
                'name' => $LNG['tech'][$ID],
                'type' => 'officer',
            ];
        }

        return json_encode(
            $objectifs,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }


    /**
     * Typ eines Quest-Ziels bestimmen.
     */
    private function getQuestObjectiveType(int $objectiveId): ?string
    {
        global $reslist;

        if (
            isset($reslist['build']) &&
            in_array($objectiveId, $reslist['build'], true)
        ) {
            return 'build';
        }

        if (
            isset($reslist['tech']) &&
            in_array($objectiveId, $reslist['tech'], true)
        ) {
            return 'research';
        }

        if (
            isset($reslist['fleet']) &&
            in_array($objectiveId, $reslist['fleet'], true)
        ) {
            return 'fleet';
        }

        if (
            isset($reslist['defense']) &&
            in_array($objectiveId, $reslist['defense'], true)
        ) {
            return 'defense';
        }

        if (
            isset($reslist['officier']) &&
            in_array($objectiveId, $reslist['officier'], true)
        ) {
            return 'officer';
        }

        return null;
    }


    /**
     * Titel und Beschreibung einer automatischen Quest
     * in der aktuell geladenen Sprache erzeugen.
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
         * Alte Quest:
         * Text wurde früher direkt gespeichert.
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
            !in_array($type, $allowedTypes, true) ||
            !isset($LNG['tech'][$objectiveId])
        ) {
            return [
                'title'       => $storedTitle,
                'description' => $storedDescription,
            ];
        }

        $objectName = $LNG['tech'][$objectiveId];

        switch ($type) {

            case 'build':

                $title = sprintf(
                    $LNG['quest_task_build'],
                    $objectName
                );

                $description = sprintf(
                    $LNG['quest_desc_build'],
                    $objectName,
                    $objectiveLevel
                );

                break;


            case 'research':

                $title = sprintf(
                    $LNG['quest_task_research'],
                    $objectName
                );

                $description = sprintf(
                    $LNG['quest_desc_research'],
                    $objectName,
                    $objectiveLevel
                );

                break;


            case 'fleet':

                $title = sprintf(
                    $LNG['quest_task_fleet'],
                    $objectName
                );

                $description = sprintf(
                    $LNG['quest_desc_fleet'],
                    $objectiveLevel,
                    $objectName
                );

                break;


            case 'defense':

                $title = sprintf(
                    $LNG['quest_task_defense'],
                    $objectName
                );

                $description = sprintf(
                    $LNG['quest_desc_defense'],
                    $objectiveLevel,
                    $objectName
                );

                break;


            case 'officer':

                $title = sprintf(
                    $LNG['quest_task_officer'],
                    $objectName
                );

                $description = sprintf(
                    $LNG['quest_desc_officer'],
                    $objectName,
                    $objectiveLevel
                );

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
     * Daten einer Quest für das Bearbeiten-Modal.
     */
    public function modalQuest($db, $data)
    {
        $response = [];

        $questID = isset($data['questID'])
            ? (int) $data['questID']
            : 0;

        if ($questID <= 0) {

            $response["alert"] = "danger";
            $response["message"] = "Diese Quest existiert nicht.";

        } else {

            $sql = "SELECT *
                    FROM %%QUESTS_LISTS%%
                    WHERE questsID = :questsID;";

            $result = $db->selectSingle(
                $sql,
                [
                    ":questsID" => $questID
                ]
            );

            if (!empty($result)) {

                $timestamp =
                    (int) $result['quest_time_finish_event'];

                if ($timestamp > 0) {

                    $date = new DateTime();
                    $date->setTimestamp($timestamp);

                    $eventFinish =
                        $date->format('Y-m-d\TH:i');

                } else {

                    $eventFinish = '';
                }

                /*
                 * Für das Modal brauchen wir hauptsächlich
                 * Ziel, Anzahl, Belohnung, Kategorie usw.
                 *
                 * quest_title und quest_description bleiben
                 * aus Kompatibilitätsgründen vorhanden.
                 */
                $response[] = [
                    'questsID' =>
                        $result['questsID'],

                    'questsCategories' =>
                        $result['questsCategories'],

                    'quest_title' =>
                        $result['quest_title'],

                    'quest_description' =>
                        $result['quest_description'],

                    'quest_objectif' =>
                        $result['quest_objectif'],

                    'quest_objectif_level' =>
                        $result['quest_objectif_level'],

                    'quest_points_reward' =>
                        $result['quest_points_reward'],

                    'quest_metal_reward' =>
                        $result['quest_metal_reward'],

                    'quest_crystal_reward' =>
                        $result['quest_crystal_reward'],

                    'quest_deuterium_reward' =>
                        $result['quest_deuterium_reward'],

                    'quest_darkmatter_reward' =>
                        $result['quest_darkmatter_reward'],

                    'quest_actif' =>
                        $result['quest_actif'],

                    'quest_event' =>
                        $result['quest_event'],

                    'quest_time_finish_event' =>
                        $eventFinish,
                ];

            } else {

                $response["alert"] = "danger";
                $response["message"] = "Diese Quest existiert nicht.";
            }
        }

        echo json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        exit;
    }


    /**
     * Neue Quest erstellen.
     */
    public function addQuest($db, $data)
    {
        $response = [];

        $categoriesId = isset($data['categories_id'])
            ? (int) $data['categories_id']
            : 0;

        $questObj = isset($data['quest_obj'])
            ? (int) $data['quest_obj']
            : 0;

        $questObjLevel = isset($data['quest_obj_level'])
            ? (int) $data['quest_obj_level']
            : 0;


        /*
         * Pflichtfelder
         */
        if (
            $categoriesId <= 0 ||
            $questObj <= 0 ||
            $questObjLevel <= 0
        ) {

            $response['alert'] = 'danger';

            $response['message'] =
                'Bitte Kategorie, Aufgabe und Anzahl/Stufe auswählen.';

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
            exit;
        }


        /*
         * Kategorie prüfen
         */
        $sql = "SELECT questsCategories
                FROM %%QUESTS_CATEGORIES%%
                WHERE questsCategories = :idCat;";

        $category = $db->selectSingle(
            $sql,
            [
                ':idCat' => $categoriesId
            ]
        );

        if (empty($category)) {

            $response['alert'] = 'danger';

            $response['message'] =
                'Die ausgewählte Kategorie existiert nicht.';

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
            exit;
        }


        /*
         * Art der Aufgabe anhand des Ziels bestimmen.
         */
        $questType =
            $this->getQuestObjectiveType($questObj);

        if ($questType === null) {

            $response['alert'] = 'danger';

            $response['message'] =
                'Das ausgewählte Quest-Ziel ist ungültig.';

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
            exit;
        }


        /*
         * WICHTIG:
         *
         * Wir speichern KEINEN deutschen Titel.
         *
         * Dadurch kann die Spielseite später
         * dieselbe Quest in jeder Sprache darstellen.
         */
        $questTitle =
            'AUTO:' . $questType;

        $questDescription =
            'AUTO';


        /*
         * Aktiv / Event
         */
        $questActif =
            !empty($data['quest_actif'])
                ? 1
                : 0;

        $questEvent =
            !empty($data['quest_event'])
                ? 1
                : 0;


        /*
         * Event-Ende
         */
        $eventTime = !empty($data['quest_time_finish_event'])
            ? strtotime($data['quest_time_finish_event'])
            : false;

        $questTimeFinishEvent =
            $eventTime === false
                ? 0
                : (int) $eventTime;


        /*
         * Belohnungen
         */
        $points = isset($data['quest_redward_points'])
            ? max(0, (float) $data['quest_redward_points'])
            : 0;

        $metal = isset($data['quest_redward_metal'])
            ? max(0, (float) $data['quest_redward_metal'])
            : 0;

        $crystal = isset($data['quest_redward_crystal'])
            ? max(0, (float) $data['quest_redward_crystal'])
            : 0;

        $deuterium = isset($data['quest_redward_deuterium'])
            ? max(0, (float) $data['quest_redward_deuterium'])
            : 0;

        $darkmatter = isset($data['quest_redward_darkmatter'])
            ? max(0, (float) $data['quest_redward_darkmatter'])
            : 0;


        /*
         * Speichern
         */
        $sqlInsert = "INSERT INTO %%QUESTS_LISTS%% SET
            questsCategories = :questsCategories,
            quest_title = :quest_title,
            quest_description = :quest_description,
            quest_objectif = :quest_objectif,
            quest_objectif_level = :quest_objectif_level,
            quest_points_reward = :quest_points_reward,
            quest_metal_reward = :quest_metal_reward,
            quest_crystal_reward = :quest_crystal_reward,
            quest_deuterium_reward = :quest_deuterium_reward,
            quest_darkmatter_reward = :quest_darkmatter_reward,
            quest_actif = :quest_actif,
            quest_created = :quest_created,
            quest_event = :quest_event,
            quest_time_finish_event = :quest_time_finish_event;";

        $db->insert(
            $sqlInsert,
            [
                ':questsCategories' =>
                    $categoriesId,

                ':quest_title' =>
                    $questTitle,

                ':quest_description' =>
                    $questDescription,

                ':quest_objectif' =>
                    $questObj,

                ':quest_objectif_level' =>
                    $questObjLevel,

                ':quest_points_reward' =>
                    $points,

                ':quest_metal_reward' =>
                    $metal,

                ':quest_crystal_reward' =>
                    $crystal,

                ':quest_deuterium_reward' =>
                    $deuterium,

                ':quest_darkmatter_reward' =>
                    $darkmatter,

                ':quest_actif' =>
                    $questActif,

                ':quest_created' =>
                    TIMESTAMP,

                ':quest_event' =>
                    $questEvent,

                ':quest_time_finish_event' =>
                    $questTimeFinishEvent,
            ]
        );

        $response['alert'] =
            'success';

        $response['message'] =
            'Quest wurde erfolgreich erstellt.';

        echo json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        exit;
    }


    /**
     * Quest bearbeiten.
     */
    public function editQuest($db, $data)
    {
        $response = [];

        $questId = isset($data['id'])
            ? (int) $data['id']
            : 0;

        $categoriesId = isset($data['categories_id'])
            ? (int) $data['categories_id']
            : 0;

        $questObj = isset($data['quest_obj'])
            ? (int) $data['quest_obj']
            : 0;

        $questObjLevel = isset($data['quest_obj_level'])
            ? (int) $data['quest_obj_level']
            : 0;


        if (
            $questId <= 0 ||
            $categoriesId <= 0 ||
            $questObj <= 0 ||
            $questObjLevel <= 0
        ) {

            $response['alert'] = 'danger';

            $response['message'] =
                'Bitte Kategorie, Aufgabe und Anzahl/Stufe auswählen.';

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
            exit;
        }


        /*
         * Prüfen, ob Quest existiert.
         */
        $sql = "SELECT questsID
                FROM %%QUESTS_LISTS%%
                WHERE questsID = :questsID;";

        $existingQuest = $db->selectSingle(
            $sql,
            [
                ':questsID' => $questId
            ]
        );

        if (empty($existingQuest)) {

            $response['alert'] =
                'danger';

            $response['message'] =
                'Diese Quest existiert nicht.';

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
            exit;
        }


        /*
         * Kategorie prüfen.
         */
        $sql = "SELECT questsCategories
                FROM %%QUESTS_CATEGORIES%%
                WHERE questsCategories = :idCat;";

        $category = $db->selectSingle(
            $sql,
            [
                ':idCat' => $categoriesId
            ]
        );

        if (empty($category)) {

            $response['alert'] =
                'danger';

            $response['message'] =
                'Die ausgewählte Kategorie existiert nicht.';

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
            exit;
        }


        /*
         * Typ bestimmen.
         */
        $questType =
            $this->getQuestObjectiveType($questObj);

        if ($questType === null) {

            $response['alert'] =
                'danger';

            $response['message'] =
                'Das ausgewählte Quest-Ziel ist ungültig.';

            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
            exit;
        }


        $questTitle =
            'AUTO:' . $questType;

        $questDescription =
            'AUTO';


        /*
         * Aktiv / Event
         */
        $questActif =
            !empty($data['quest_actif'])
                ? 1
                : 0;

        $questEvent =
            !empty($data['quest_event'])
                ? 1
                : 0;


        /*
         * Event-Zeit
         */
        $eventTime = !empty($data['quest_time_finish_event'])
            ? strtotime($data['quest_time_finish_event'])
            : false;

        $questTimeFinishEvent =
            $eventTime === false
                ? 0
                : (int) $eventTime;


        /*
         * Belohnungen
         */
        $points = isset($data['quest_redward_points'])
            ? max(0, (float) $data['quest_redward_points'])
            : 0;

        $metal = isset($data['quest_redward_metal'])
            ? max(0, (float) $data['quest_redward_metal'])
            : 0;

        $crystal = isset($data['quest_redward_crystal'])
            ? max(0, (float) $data['quest_redward_crystal'])
            : 0;

        $deuterium = isset($data['quest_redward_deuterium'])
            ? max(0, (float) $data['quest_redward_deuterium'])
            : 0;

        $darkmatter = isset($data['quest_redward_darkmatter'])
            ? max(0, (float) $data['quest_redward_darkmatter'])
            : 0;


        /*
         * Aktualisieren
         */
        $sqlUpdate = "UPDATE %%QUESTS_LISTS%% SET
            questsCategories = :questsCategories,
            quest_title = :quest_title,
            quest_description = :quest_description,
            quest_objectif = :quest_objectif,
            quest_objectif_level = :quest_objectif_level,
            quest_points_reward = :quest_points_reward,
            quest_metal_reward = :quest_metal_reward,
            quest_crystal_reward = :quest_crystal_reward,
            quest_deuterium_reward = :quest_deuterium_reward,
            quest_darkmatter_reward = :quest_darkmatter_reward,
            quest_actif = :quest_actif,
            quest_created = :quest_created,
            quest_event = :quest_event,
            quest_time_finish_event = :quest_time_finish_event
            WHERE questsID = :questsID;";

        $db->update(
            $sqlUpdate,
            [
                ':questsID' =>
                    $questId,

                ':questsCategories' =>
                    $categoriesId,

                ':quest_title' =>
                    $questTitle,

                ':quest_description' =>
                    $questDescription,

                ':quest_objectif' =>
                    $questObj,

                ':quest_objectif_level' =>
                    $questObjLevel,

                ':quest_points_reward' =>
                    $points,

                ':quest_metal_reward' =>
                    $metal,

                ':quest_crystal_reward' =>
                    $crystal,

                ':quest_deuterium_reward' =>
                    $deuterium,

                ':quest_darkmatter_reward' =>
                    $darkmatter,

                ':quest_actif' =>
                    $questActif,

                ':quest_created' =>
                    TIMESTAMP,

                ':quest_event' =>
                    $questEvent,

                ':quest_time_finish_event' =>
                    $questTimeFinishEvent,
            ]
        );

        $response['alert'] =
            'success';

        $response['message'] =
            'Quest wurde erfolgreich aktualisiert.';

        echo json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        exit;
    }


    /**
     * Quest löschen.
     */
    public function deleteQuest($db, $data)
    {
        $response = [];

        $id = isset($data['id'])
            ? (int) $data['id']
            : 0;

        if ($id <= 0) {

            $response["alert"] = "danger";
            $response["message"] = "Bitte eine gültige Quest-ID angeben.";

        } else {

            $sql = "SELECT questsID
                    FROM %%QUESTS_LISTS%%
                    WHERE questsID = :idQuest;";

            $result = $db->selectSingle(
                $sql,
                [
                    ":idQuest" => $id
                ]
            );

            if (empty($result)) {

                $response["alert"] = "danger";
                $response["message"] = "Diese Quest existiert nicht.";

            } else {

                $sqlDelete = "DELETE FROM %%QUESTS_LISTS%%
                              WHERE questsID = :idQuest;";

                $db->delete(
                    $sqlDelete,
                    [
                        ":idQuest" => $id
                    ]
                );

                $response["alert"] = "success";
                $response["message"] = "Quest wurde erfolgreich gelöscht.";
            }
        }

        echo json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        exit;
    }
}