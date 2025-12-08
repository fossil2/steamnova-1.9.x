<?php

class ShowTutorialPage extends AbstractGamePage
{
    public static $requireModule = MODULE_SUPPORT;

    private $missions = [];

    private const OK  = '<img src="styles/resource/images/gruener-haken.png">';
    private const BAD = '<img src="styles/resource/images/roter-haken.png">';

    public function __construct()
    {
        parent::__construct();

        // ----------------------------
        // MISSIONS
        // ----------------------------
        $this->missions = [
            1 => [
                'flag'     => 'tut_m1',
                'reward'   => 1000,
                'tpl'      => 'mission_1.tpl',
                'lng_done' => 'tut_m1_ready',
                'redirect' => 'game.php?page=tutorial&mode=m2',
                'steps' => [
                    ['type'=>'planet','field'=>'metal_mine','op'=>'>=','value'=>4,'step'=>1],
                    ['type'=>'planet','field'=>'crystal_mine','op'=>'>=','value'=>2,'step'=>2],
                    ['type'=>'planet','field'=>'solar_plant','op'=>'>=','value'=>4,'step'=>3],
                ],
            ],

            2 => [
                'flag'     => 'tut_m2',
                'reward'   => 1500,
                'tpl'      => 'mission_2.tpl',
                'lng_done' => 'tut_m2_ready',
                'redirect' => 'game.php?page=tutorial&mode=m3',
                'steps' => [
                    ['type'=>'planet','field'=>'deuterium_sintetizer','op'=>'>=','value'=>2,'step'=>1],
                    ['type'=>'planet','field'=>'robot_factory','op'=>'>=','value'=>2,'step'=>2],
                    ['type'=>'planet','field'=>'hangar','op'=>'>=','value'=>1,'step'=>3],
                    ['type'=>'planet','field'=>'misil_launcher','op'=>'>=','value'=>10,'step'=>4],
                ],
            ],

            3 => [
                'flag' => 'tut_m3',
                'reward'=>1800,
                'tpl'=>'mission_3.tpl',
                'lng_done'=>'tut_m3_ready',
                'redirect'=>'game.php?page=tutorial&mode=m4',
                'steps'=>[
                    ['type'=>'planet','field'=>'metal_mine','op'=>'>=','value'=>10,'step'=>1],
                    ['type'=>'planet','field'=>'crystal_mine','op'=>'>=','value'=>8,'step'=>2],
                    ['type'=>'planet','field'=>'deuterium_sintetizer','op'=>'>=','value'=>5,'step'=>3],
                ],
            ],

            4 => [
                'flag'=>'tut_m4',
                'reward'=>2000,
                'tpl'=>'mission_4.tpl',
                'lng_done'=>'tut_m4_ready',
                'redirect'=>'game.php?page=tutorial&mode=m5',
                'steps'=>[
                    ['type'=>'planet','field'=>'laboratory','op'=>'>=','value'=>1,'step'=>1],
                    ['type'=>'planet','field'=>'hangar','op'=>'>=','value'=>4,'step'=>2],
                    ['type'=>'user','field'=>'combustion_tech','op'=>'>=','value'=>2,'step'=>3],
                    ['type'=>'planet','field'=>'small_ship_cargo','op'=>'>=','value'=>5,'step'=>4],
                ],
            ],

            5 => [
                'flag'=>'tut_m5',
                'reward'=>2500,
                'tpl'=>'mission_5.tpl',
                'lng_done'=>'tut_m5_ready',
                'redirect'=>'game.php?page=tutorial&mode=m6',
                'steps'=>[
                    ['type'=>'user','field'=>'ally_id','op'=>'>','value'=>0,'step'=>1],
                    [
                        'type'=>'sql',
                        'sql'=>"SELECT COUNT(*) AS cnt FROM %%BUDDY%% WHERE sender = :id",
                        'op'=>'>=','value'=>1,'step'=>2,
                    ],
                ],
            ],

            6 => [
                'flag'=>'tut_m6',
                'reward'=>2600,
                'tpl'=>'mission_6.tpl',
                'lng_done'=>'tut_m6_ready',
                'redirect'=>'game.php?page=tutorial&mode=m7',
                'steps'=>[
                    ['type'=>'planet','field'=>'deuterium_store','op'=>'>=','value'=>1,'step'=>1],
                    ['type'=>'planet','field'=>'metal_store','op'=>'>=','value'=>1,'step'=>2],
                    ['type'=>'planet','field'=>'crystal_store','op'=>'>=','value'=>1,'step'=>3],
                    ['type'=>'planet','field'=>'small_protection_shield','op'=>'>=','value'=>1,'step'=>4],
                    ['type'=>'planet','field'=>'big_protection_shield','op'=>'>=','value'=>1,'step'=>5],
                ],
            ],

            7 => [
                'flag'=>'tut_m7',
                'reward'=>2700,
                'tpl'=>'mission_7.tpl',
                'lng_done'=>'tut_m7_ready',
                'redirect'=>'game.php?page=tutorial&mode=m8',
                'steps'=>[
                    ['type'=>'planet','field'=>'spy_sonde','op'=>'>=','value'=>1,'step'=>1],
                    ['type'=>'user','field'=>'tut_m7_2','op'=>'>=','value'=>1,'step'=>2],
                    ['type'=>'user','field'=>'spy_tech','op'=>'>=','value'=>2,'step'=>3],
                ],
            ],

            8 => [
                'flag'=>'tut_m8',
                'reward'=>2800,
                'tpl'=>'mission_8.tpl',
                'lng_done'=>'tut_m8_ready',
                'redirect'=>'game.php?page=tutorial&mode=m9',
                'steps'=>[
                    ['type'=>'planet','field'=>'colonizer','op'=>'>=','value'=>2,'step'=>1],
                    [
                        'type'=>'sql',
                        'sql'=>"SELECT COUNT(*) AS cnt FROM %%PLANETS%% WHERE id_owner = :id",
                        'op'=>'>=','value'=>2,'step'=>2,
                    ],
                    ['type'=>'planet','field'=>'big_ship_cargo','op'=>'>=','value'=>10,'step'=>3],
                ],
            ],

            9 => [
                'flag'=>'tut_m9',
                'reward'=>5000,
                'tpl'=>'mission_9.tpl',
                'lng_done'=>'tut_compleat',
                'redirect'=>'game.php?page=achievements',
                'steps'=>[
                    ['type'=>'planet','field'=>'recycler','op'=>'>=','value'=>25,'step'=>1],
                    ['type'=>'user','field'=>'tut_m9_2','op'=>'>=','value'=>1,'step'=>2],
                    ['type'=>'planet','field'=>'battle_ship','op'=>'>=','value'=>100,'step'=>3],
                    ['type'=>'energy','op'=>'>=','value'=>2000,'step'=>4],
                ],
            ],
        ];
    }

    // ENUM helper
    private function isEnumTrue($v) { return ($v === '1' || $v === 1); }
    private function isEnumFalse($v) { return ($v === '0' || $v === 0 || $v === null || $v === ''); }

    // ---------------------------------------------------
    // START PAGE
    // ---------------------------------------------------
    public function show()
    {
        global $USER, $LNG;

        // Icons
        foreach ($this->missions as $id => $m) {
            $done = $this->isEnumTrue($USER[$m['flag']] ?? '0');

            $this->tplObj->assign_vars([
                "Si{$id}" => $done ? self::OK : '',
                "No{$id}" => $done ? '' : self::BAD,
            ]);
        }

        $this->tplObj->assign_vars(['tut_welcome' => $LNG['tut_welcome']]);

        // Startseite wenn Tutorial nicht gestartet
        if ($this->isEnumFalse($USER['started_tut'] ?? '0') && !isset($_GET['mode'])) {
            return $this->display('inizio.tpl');
        }

        // Start markieren
        if ($this->isEnumFalse($USER['started_tut'] ?? '0') && isset($_GET['mode'])) {
            Database::get()->update(
                "UPDATE %%USERS%% SET started_tut = '1' WHERE id = :id",
                [':id' => $USER['id']]
            );
            $USER['started_tut'] = '1';
        }

        // Erste offene Mission öffnen
        foreach ($this->missions as $id => $m) {
            if ($this->isEnumFalse($USER[$m['flag']] ?? '0')) {
                return $this->redirectTo("game.php?page=tutorial&mode=m{$id}");
            }
        }

        return $this->redirectTo("game.php?page=tutorial&mode=m9");
    }

    // dispatcher
    public function __call($name, $args)
    {
        if (preg_match('/^m([1-9])$/', $name, $match)) {
            return $this->runMission((int)$match[1]);
        }
        $this->printMessage("Ungültige Mission.");
    }

    // ---------------------------------------------------
    // MISSION AUSFÜHREN
    // ---------------------------------------------------
    private function runMission(int $id)
    {
        global $USER, $PLANET, $LNG;

        $mission = $this->missions[$id];
        $db      = Database::get();

        // Tutorial starten falls nötig
        if ($this->isEnumFalse($USER['started_tut'] ?? '0')) {
            $db->update("UPDATE %%USERS%% SET started_tut = '1' WHERE id = :id", [
                ':id' => $USER['id']
            ]);
            $USER['started_tut'] = '1';
        }

        // Navigation berechnen
        $prev = ($id > 1) ? $id - 1 : null;
        $next = ($id < count($this->missions)) ? $id + 1 : null;

        $this->tplObj->assign_vars([
            'prevMission' => $prev,
            'nextMission' => $next,
        ]);

        // Navigation Icons
        foreach ($this->missions as $mid => $m) {
            $done = $this->isEnumTrue($USER[$m['flag']] ?? '0');
            $this->tplObj->assign_vars([
                "Si{$mid}" => $done ? self::OK : '',
                "No{$mid}" => $done ? '' : self::BAD,
            ]);
        }

        $flagName   = $mission['flag'];
        $isFinished = $this->isEnumTrue($USER[$flagName] ?? '0');

        $this->tplObj->assign_vars([
            "livello{$id}" => $isFinished ? $LNG['tut_ready'] : $LNG['tut_not_ready'],
        ]);

        // Steps prüfen
        $stepsOk = true;

        foreach ($mission['steps'] as $s) {
            $ok = false;

            switch ($s['type']) {
                case 'planet':
                    $ok = $this->compare($PLANET[$s['field']] ?? 0, $s['op'], $s['value']);
                    break;

                case 'user':
                    $ok = $this->compare($USER[$s['field']] ?? 0, $s['op'], $s['value']);
                    break;

                case 'sql':
                    $row = $db->selectSingle($s['sql'], [':id' => $USER['id']]);
                    $ok  = $this->compare($row['cnt'] ?? 0, $s['op'], $s['value']);
                    break;

                case 'energy':
                    $energy = (int)$PLANET['energy'] + (int)$PLANET['energy_used'];
                    $ok     = $this->compare($energy, $s['op'], $s['value']);
                    break;
            }

            // Variablen zuweisen
            $this->tplObj->assign_vars([
                "Si_m{$id}_{$s['step']}" => $ok ? self::OK : '',
                "No_m{$id}_{$s['step']}" => $ok ? '' : self::BAD,
            ]);

            if (!$ok) $stepsOk = false;
        }

        // Mission möglich?
        $this->tplObj->assign_vars([
            'missionReady' => $stepsOk && !$isFinished,
        ]);

        // Mission abschließen
        if (isset($_POST['complete']) && $stepsOk && !$isFinished) {

            $db->update(
                "UPDATE %%USERS%% 
                 SET {$flagName} = '1',
                     darkmatter = darkmatter + :dm
                 WHERE id = :id",
                [
                    ':id' => $USER['id'],
                    ':dm' => $mission['reward']
                ]
            );

            $USER[$flagName] = '1';
            $USER['darkmatter'] += $mission['reward'];

            return $this->printMessage($LNG[$mission['lng_done']], $mission['redirect']);
        }

        return $this->display($mission['tpl']);
    }

    private function compare($a, string $op, $b)
    {
        return match ($op) {
            '>=' => $a >= $b,
            '<=' => $a <= $b,
            '>'  => $a >  $b,
            '<'  => $a <  $b,
            '==' => $a == $b,
            default => false,
        };
    }
}

?>
