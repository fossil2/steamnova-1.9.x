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

        $this->missions = [

            1 => [
                'flag'=>'tut_m1',
                'reward'=>['metal'=>600,'crystal'=>400,'deuterium'=>0,'darkmatter'=>0],
                'tpl'=>'mission_1.tpl',
                'lng_done'=>'tut_m1_ready',
                'redirect'=>'game.php?page=tutorial&mode=m2',
                'steps'=>[
                    ['type'=>'planet','field'=>'metal_mine','op'=>'>=','value'=>4,'step'=>1],
                    ['type'=>'planet','field'=>'crystal_mine','op'=>'>=','value'=>2,'step'=>2],
                    ['type'=>'planet','field'=>'solar_plant','op'=>'>=','value'=>4,'step'=>3],
                ],
            ],

            2 => [
                'flag'=>'tut_m2',
                'reward'=>['metal'=>1200,'crystal'=>800,'deuterium'=>200,'darkmatter'=>0],
                'tpl'=>'mission_2.tpl',
                'lng_done'=>'tut_m2_ready',
                'redirect'=>'game.php?page=tutorial&mode=m3',
                'steps'=>[
                    ['type'=>'planet','field'=>'deuterium_sintetizer','op'=>'>=','value'=>2,'step'=>1],
                    ['type'=>'planet','field'=>'robot_factory','op'=>'>=','value'=>2,'step'=>2],
                    ['type'=>'planet','field'=>'hangar','op'=>'>=','value'=>1,'step'=>3],
                    ['type'=>'planet','field'=>'misil_launcher','op'=>'>=','value'=>10,'step'=>4],
                ],
            ],

            3 => [
                'flag'=>'tut_m3',
                'reward'=>['metal'=>1700,'crystal'=>1000,'deuterium'=>500,'darkmatter'=>0],
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
                'reward'=>['metal'=>2000,'crystal'=>1400,'deuterium'=>600,'darkmatter'=>25],
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
        'reward'=>['metal'=>5000,'crystal'=>2500,'deuterium'=>1000,'darkmatter'=>50],
        'tpl'=>'mission_5.tpl',
        'lng_done'=>'tut_m5_ready',
        'redirect'=>'game.php?page=tutorial&mode=m6',
        'steps'=>[
            ['type'=>'user','field'=>'ally_id','op'=>'>','value'=>0,'step'=>1],
            ['type'=>'sql','sql'=>"SELECT COUNT(*) cnt FROM %%BUDDY%% WHERE sender = :id",'op'=>'>=','value'=>1,'step'=>2],
        ],
    ],

    6 => [
        'flag'=>'tut_m6',
        'reward'=>['metal'=>0,'crystal'=>0,'deuterium'=>500,'darkmatter'=>50],
        'tpl'=>'mission_6.tpl',
        'lng_done'=>'tut_m6_ready',
        'redirect'=>'game.php?page=tutorial&mode=m7',
        'steps'=>[
            ['type'=>'planet','field'=>'deuterium_store','op'=>'>=','value'=>1,'step'=>1],
            ['type'=>'planet','field'=>'metal_store','op'=>'>=','value'=>1,'step'=>2],
            ['type'=>'planet','field'=>'crystal_store','op'=>'>=','value'=>1,'step'=>3],
        ],
    ],

    7 => [
        'flag'=>'tut_m7',
        'reward'=>['metal'=>0,'crystal'=>0,'deuterium'=>0,'darkmatter'=>100],
        'tpl'=>'mission_7.tpl',
        'lng_done'=>'tut_m7_ready',
        'redirect'=>'game.php?page=tutorial&mode=m8',
        'steps'=>[
            ['type'=>'planet','field'=>'spy_sonde','op'=>'>=','value'=>1,'step'=>1],
            ['type'=>'user','field'=>'spy_tech','op'=>'>=','value'=>2,'step'=>2],
            [
                'type'=>'sql',
                'sql'=>"SELECT COUNT(*) cnt FROM %%MESSAGES%% WHERE message_type = 1 AND message_owner = :id",
                'op'=>'>=','value'=>1,'step'=>3
            ],
        ],
    ],

    8 => [
        'flag'=>'tut_m8',
        'reward'=>['metal'=>0,'crystal'=>500,'deuterium'=>1000,'darkmatter'=>175],
        'tpl'=>'mission_8.tpl',
        'lng_done'=>'tut_m8_ready',
        'redirect'=>'game.php?page=tutorial&mode=m9',
        'steps'=>[
            ['type'=>'planet','field'=>'recycler','op'=>'>=','value'=>5,'step'=>1],
            [
                'type'=>'sql',
                'sql'=>"SELECT COUNT(*) cnt FROM %%PLANETS%% WHERE id_owner = :id",
                'op'=>'>=','value'=>2,'step'=>2
            ],
            ['type'=>'planet','field'=>'big_ship_cargo','op'=>'>=','value'=>5,'step'=>3],
        ],
    ],

    9 => [
    'flag'=>'tut_m9',
    'reward'=>['metal'=>5000,'crystal'=>0,'deuterium'=>0,'darkmatter'=>250],
    'tpl'=>'mission_9.tpl',
    'lng_done'=>'tut_compleat',
    'redirect'=>'game.php?page=achievements',
    'steps'=>[
        ['type'=>'planet','field'=>'battle_ship','op'=>'>=','value'=>50,'step'=>1],
        ['type'=>'user','field'=>'energy_tech','op'=>'>=','value'=>3,'step'=>2],
        ['type'=>'energy','op'=>'>=','value'=>2000,'step'=>3],

        
        [
            'type'=>'sql',
            'sql'=>"SELECT COUNT(*) cnt FROM %%PLANETS%% WHERE id_owner = :id",
            'op'=>'>=','value'=>2,
            'step'=>4
        ],
    ],
],
   ];   
}  

public function show()
{
    global $USER;

    
    if (isset($_GET['mode']) && $_GET['mode'] === 'intro') {
        return $this->display('inizio.tpl');
    }

    
    if (($USER['started_tut'] ?? '0') !== '1') {
        Database::get()->update(
            "UPDATE %%USERS%% SET started_tut = '1' WHERE id = :id",
            [':id' => $USER['id']]
        );
        $USER['started_tut'] = '1';

        return $this->display('inizio.tpl');
    }

  
    foreach ($this->missions as $id => $mission) {
        if (($USER[$mission['flag']] ?? '0') !== '1') {
            return $this->redirectTo("game.php?page=tutorial&mode=m{$id}");
        }
    }

    
    return $this->redirectTo("game.php?page=achievements");
}


    private function isEnumTrue($v){ return ($v === '1' || $v === 1); }
    private function isEnumFalse($v){ return ($v === '0' || $v === 0 || $v === null || $v === ''); }

    
    public function __call($name, $args)
    {
        if (preg_match('/^m([0-9]+)$/', $name, $m)) {
            return $this->runMission((int)$m[1]);
        }
        return $this->printMessage("Ungültige Mission.");
    }

    // ---------------------------------------------------
    // MISSION
    // ---------------------------------------------------
    private function runMission(int $id)
    {
        global $USER, $PLANET, $LNG;

        if (!isset($this->missions[$id])) {
            return $this->printMessage("Mission existiert nicht.");
        }

        $db      = Database::get();
        $mission = $this->missions[$id];
        $flag    = $mission['flag'];
        
        
$this->tplObj->assign_vars([
    'reward_metal'      => $mission['reward']['metal'] ?? 0,
    'reward_crystal'    => $mission['reward']['crystal'] ?? 0,
    'reward_deuterium'  => $mission['reward']['deuterium'] ?? 0,
    'reward_darkmatter' => $mission['reward']['darkmatter'] ?? 0,
]);


        foreach ($this->missions as $mid => $m) {
            $done = $this->isEnumTrue($USER[$m['flag']] ?? '0');
            $this->tplObj->assign_vars([
                "livello{$mid}" => $done ? $LNG['tut_ready'] : $LNG['tut_not_ready'],
            ]);
        }

        $isFinished = $this->isEnumTrue($USER[$flag] ?? '0');

        
        if (empty($_SESSION['tut_token'])) {
            $_SESSION['tut_token'] = bin2hex(random_bytes(16));
        }
        $this->tplObj->assign('tut_token', $_SESSION['tut_token']);

        
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
                    $row = $db->selectSingle($s['sql'], [':id'=>$USER['id']]);
                    $ok  = $this->compare($row['cnt'] ?? 0, $s['op'], $s['value']);
                    break;
            }

            $this->tplObj->assign_vars([
                "Si_m{$id}_{$s['step']}" => $ok ? self::OK : '',
                "No_m{$id}_{$s['step']}" => $ok ? '' : self::BAD,
            ]);

            if (!$ok) {
                $stepsOk = false;
            }
        }

        $this->tplObj->assign('missionReady', $stepsOk && !$isFinished);

        
        if (
            isset($_POST['complete']) &&
            $stepsOk &&
            !$isFinished &&
            isset($_POST['tut_token']) &&
            hash_equals($_SESSION['tut_token'], $_POST['tut_token'])
        ) {
            unset($_SESSION['tut_token']);

            $r = $mission['reward'];

          $db->update(
    "UPDATE %%USERS%%
     SET {$flag} = '1',
         darkmatter = darkmatter + :dm
     WHERE id = :uid AND {$flag} = '0'",
    [
        ':uid' => $USER['id'],
        ':dm'  => $r['darkmatter']
    ]
);


$db->update(
    "UPDATE %%PLANETS%%
     SET metal = metal + :m,
         crystal = crystal + :c,
         deuterium = deuterium + :d
     WHERE id = :pid",
    [
        ':pid' => $PLANET['id'],
        ':m'   => $r['metal'],
        ':c'   => $r['crystal'],
        ':d'   => $r['deuterium']
    ]
);

            $USER[$flag] = '1';
            $PLANET['metal']     += $r['metal'];
            $PLANET['crystal']   += $r['crystal'];
            $PLANET['deuterium'] += $r['deuterium'];
            $USER['darkmatter']  += $r['darkmatter'];

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
