<?php

/**
 * @mods Quests
 * @version 1.0
 * @author Danter14
 * @licence MIT
 * @package 2Moons
 * @version 1.8 - 1.9 - 2.0
 */

class ShowQuestsPage extends AbstractGamePage
{
    public static $requireModule = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        global $LNG;

        $db = Database::get();

        $list_cat = [];

        $sql_cat_list = "SELECT questsCategories FROM %%QUESTS_CATEGORIES%% ORDER BY questsCategories;";
        $result_cat_list = $db->select($sql_cat_list);

        foreach ($result_cat_list as $categories) {

            $categoryId = (int) $categories['questsCategories'];
            $langKey = 'quest_categorie_' . $categoryId;

            $categoryName = isset($LNG[$langKey])
                ? $LNG[$langKey]
                : 'Kategorie ' . $categoryId;

            $list_cat[] = [
                'id_cat'   => $categoryId,
                'name_cat' => $categoryName,
            ];
        }

        $this->assign([
            'result_cat_list' => $list_cat,
        ]);

        $this->tplObj->loadscript('quests.js');

        $this->display('page.quests.default.tpl');
    }
}