<?php

/**
 *  2Moons / BitNova
 *  Clean & PHP 8.2 safe Module Page
 */

class ShowModulePage extends AbstractAdminPage
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        global $LNG;

        $config = Config::get(Universe::getEmulated());
        $module = explode(';', (string) $config->moduls);

        $Modules = [];

        // Alle Modul-IDs sauber aufbauen
        for ($ID = 0; $ID < MODULE_AMOUNT; $ID++) {

            // fehlende Sprachkeys sauber abfangen
            if (!isset($LNG['modul_'.$ID])) {
                continue;
            }

            $Modules[$ID] = [
                'id'    => $ID,
                'name'  => $LNG['modul_'.$ID],
                'state' => isset($module[$ID]) ? (int) $module[$ID] : 1,
            ];
        }

        // RICHTIG nach Name sortieren (Keys bleiben erhalten!)
        uasort($Modules, static function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        $this->assign([
            'Modules' => $Modules,
        ]);

        $this->display('page.modules.default.tpl');
    }

    public function change()
    {
        global $LNG;

        $config = Config::get(Universe::getEmulated());

        $type = HTTP::_GP('type', '');
        $id   = HTTP::_GP('id', 0);
        $id   = (int) $id;

        $module = explode(';', (string) $config->moduls);

        $module[$id] = ($type === 'activate') ? 1 : 0;

        $config->moduls = implode(';', $module);
        $config->save();
        ClearCache();

        $this->printMessage(
            $LNG['settings_successful'],
            [[
                'url'   => 'admin.php?page=module&mode=show',
                'label' => $LNG['uvs_back'],
            ]]
        );
    }
}
