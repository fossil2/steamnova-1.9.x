<?php

/**
 * BotResearchAI
 *
 * Steuert die Forschung der Bot-Accounts (nur Hauptplanet).
 * - Forscht nur, wenn gerade keine Forschung läuft
 * - Prüft Labor-Level, Voraussetzungen & Rohstoffe
 * - Nutzt das bestehende Forschungs-Queue-System (b_tech_* Felder)
 */

class BotResearchAI
{
    /**
     * Welche Forschungen der Bot benutzen darf
     *
     * key = Element-ID
     */
    private static array $RESEARCH_CONFIG = [
        // ⚡ Energy Tech
        113 => [
            'label'     => 'energy_tech',
            'lab_min'   => 1,
            'max_level' => 8,
            'requires'  => [],           // keine Vorbedingungen
        ],

        // 🖥 Computer Tech
        108 => [
            'label'     => 'computer_tech',
            'lab_min'   => 1,
            'max_level' => 4,
            'requires'  => [],
        ],

        // 🚀 Combustion Drive (Verbrennungstriebwerk)
        115 => [
            'label'     => 'combustion_tech',
            'lab_min'   => 2,
            'max_level' => 6,
            // braucht EnergyTech 1
            'requires'  => [
                113 => 1,
            ],
        ],

        // 🛰 Spy Tech
        106 => [
            'label'     => 'spy_tech',
            'lab_min'   => 3,
            'max_level' => 4,
            'requires'  => [],
        ],
        
         // 🔫 Laser Tech
       120 => [
        'label'     => 'laser_tech',
        'lab_min'   => 2,   // Labor ab Level 2
        'max_level' => 6,   // nicht zu aggressiv
        'requires'  => [
          113 => 1,      // Energy Tech >= 1
      ],
    ],
        
     // 🚀 Impulstriebwerk
        117 => [
            'label'     => 'impulse_motor_tech',
            'lab_min'   => 4,
            'max_level' => 6,
            'requires'  => [
                113 => 1, // Energy Tech 1
                115 => 2, // Combustion Tech 2
            ],
        ],   
        
        124 => [
        'label'     => 'astrophysics',
        'lab_min'   => 3,       // Labor 3 reicht
        'max_level' => 2,       // fürs Erste bewusst limitiert
        'requires'  => [
         113 => 1,           // Energy Tech 1
         108 => 1, 
         117 => 3,
         106 => 3,
         
       ],
    ],
        
    ];

    /**
     * Priorität der Forschungen
     * (erste, die zulässig & bezahlbar ist, wird gestartet)
     */
    private static array $PRIORITY = [
        113, // Energy Tech
        108, // Computer Tech
        115, // Combustion
        120, // Laser Tech 
        124, // astrophysics
        117, // Impulse Drive
        106, // Spy Tech
    ];

    /**
     * Mindest-Restressourcen nach Start einer Forschung
     * (Bots sollen nicht alles leerforschen)
     */
    private const MIN_METAL   = 3000;
    private const MIN_CRYSTAL = 2000;
    private const MIN_DEUT    = 200;

    /**
     * Verhältnis Laborlevel -> max. Forschungslevel
     * z.B. 2 => Lab 5 -> max. Tech-Level 10 (zusätzlich zur Konfig-Grenze)
     */
    private const LAB_LEVEL_FACTOR = 2;

    public static function run(int $userId): void
    {
        $db = Database::get();

        // Bot-User laden
        $USER = $db->selectSingle(
            'SELECT * FROM %%USERS%% WHERE id = :uid AND is_bot = 1;',
            [':uid' => $userId]
        );

        if (empty($USER)) {
            self::log([
                'action' => 'RESEARCH_SKIP_NO_USER',
                'uid'    => $userId,
            ]);
            return;
        }

        // Urlaubsmodus: keine Aktionen
        if (!empty($USER['urlaubs_modus'])) {
            self::log([
                'action' => 'RESEARCH_SKIP_VMODE',
                'uid'    => $userId,
            ]);
            return;
        }

        $now = TIMESTAMP;

        // Läuft bereits eine Forschung?
        if (!empty($USER['b_tech']) && $USER['b_tech'] > $now) {
            self::log([
                'action' => 'RESEARCH_SKIP_ACTIVE',
                'uid'    => $userId,
                'until'  => $USER['b_tech'],
            ]);
            return;
        }

        /**
         * 1) Zufällige Verzögerung / Drosselung
         *    -> verhindert, dass alle Bots gleichzeitig forschen
         *    Hier: ca. 40% Chance, dass der Bot diesmal forscht.
         */
        $roll = mt_rand(1, 100);
        if ($roll > 40) {
            self::log([
                'action' => 'RESEARCH_DELAY_RANDOM',
                'uid'    => $userId,
                'roll'   => $roll,
            ]);
            return;
        }

        // Hauptplanet des Bots laden
        $PLANET = $db->selectSingle(
            'SELECT * FROM %%PLANETS%% 
             WHERE id_owner = :uid AND planet_type = 1 
             ORDER BY id ASC 
             LIMIT 1;',
            [':uid' => $userId]
        );

        if (empty($PLANET)) {
            self::log([
                'action' => 'RESEARCH_SKIP_NO_PLANET',
                'uid'    => $userId,
            ]);
            return;
        }

        global $resource;

        $labLevel = (int) ($PLANET[$resource[31]] ?? 0);

        self::log([
            'action'   => 'RESEARCH_RUN',
            'uid'      => $userId,
            'lab_lvl'  => $labLevel,
            'b_tech'   => (int)($USER['b_tech'] ?? 0),
        ]);

        // Kandidaten nach Priorität durchgehen
        foreach (self::$PRIORITY as $elementId) {
            if (!isset(self::$RESEARCH_CONFIG[$elementId])) {
                continue;
            }

            $config = self::$RESEARCH_CONFIG[$elementId];

            if (!self::canResearch($USER, $PLANET, $elementId, $config, $labLevel)) {
                continue;
            }

            // Versuchen, Forschung zu starten
            if (self::startResearch($USER, $PLANET, $elementId, $config)) {
                // Erfolgreich → raus
                return;
            }
        }

        // Nichts Geeignetes gefunden
        self::log([
            'action' => 'RESEARCH_NO_CANDIDATE',
            'uid'    => $userId,
        ]);
    }

    /**
     * Prüft, ob diese Forschung prinzipiell möglich ist
     */
    private static function canResearch(array $USER, array $PLANET, int $elementId, array $config, int $labLevel): bool
    {
        global $resource;

        // Labor-Level prüfen
        if ($labLevel < $config['lab_min']) {
            self::log([
                'action'   => 'RESEARCH_LAB_TOO_LOW',
                'uid'      => $USER['id'],
                'tech'     => $config['label'],
                'need'     => $config['lab_min'],
                'have'     => $labLevel,
            ]);
            return false;
        }

        $colName = $resource[$elementId] ?? null;
        if ($colName === null) {
            self::log([
                'action' => 'RESEARCH_NO_COLNAME',
                'uid'    => $USER['id'],
                'techId' => $elementId,
            ]);
            return false;
        }

        $currentLevel = (int)($USER[$colName] ?? 0);

        // Voraussetzungen (andere Forschungen)
        if (!empty($config['requires'])) {
            foreach ($config['requires'] as $reqId => $reqLevel) {
                $reqCol = $resource[$reqId] ?? null;
                if ($reqCol === null) {
                    continue;
                }
                $have = (int)($USER[$reqCol] ?? 0);
                if ($have < $reqLevel) {
                    self::log([
                        'action'   => 'RESEARCH_PREREQ_MISSING',
                        'uid'      => $USER['id'],
                        'tech'     => $config['label'],
                        'need_id'  => $reqId,
                        'need_lvl' => $reqLevel,
                        'have_lvl' => $have,
                    ]);
                    return false;
                }
            }
        }

        /**
         * 3) Dynamisches Max-Level abhängig vom Laborlevel
         *    + 4) Begrenzung abhängig von Spielerpunkten
         *    + ursprüngliche max_level-Grenze aus RESEARCH_CONFIG
         *
         *    -> kleinste der drei Grenzen wird verwendet
         */

        // Grund-Max nach Config
        $configMax = isset($config['max_level']) ? (int)$config['max_level'] : PHP_INT_MAX;

        // Labor-basiertes Max (z.B. Lab 5 -> max 10)
        $labMax = max(1, $labLevel * self::LAB_LEVEL_FACTOR);

        // Score-basierte Grenze
        $score = (int)($USER['total_points'] ?? 0); // falls nicht vorhanden -> 0

        if ($score < 5000) {
            $scoreMax = 3;
        } elseif ($score < 25000) {
            $scoreMax = 6;
        } elseif ($score < 100000) {
            $scoreMax = 12;
        } else {
            $scoreMax = 99;
        }
        
        // Sonderregel: LaserTech darf früher auf Level 3
if ($elementId === 120 && $score < 5000) {
    $scoreMax = max($scoreMax, 3);
}

if ($elementId === 117 && $score < 5000) {
    $scoreMax = max($scoreMax, 3);
}

if ($elementId === 106 && $score < 5000) {
    $scoreMax = max($scoreMax, 3);
}

if ($elementId === 124 && $score < 5000) {
    $scoreMax = max($scoreMax, 1);
}

        $finalMax = min($configMax, $labMax, $scoreMax);

        if ($currentLevel >= $finalMax) {
            self::log([
                'action'     => 'RESEARCH_MAX_LIMIT',
                'uid'        => $USER['id'],
                'tech'       => $config['label'],
                'lvl'        => $currentLevel,
                'cfg_max'    => $configMax,
                'lab_max'    => $labMax,
                'score_max'  => $scoreMax,
                'final_max'  => $finalMax,
                'score'      => $score,
                'lab_lvl'    => $labLevel,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Startet die Forschung wirklich (Rohstoffe abziehen, Queue setzen)
     */
    private static function startResearch(array $USER, array $PLANET, int $elementId, array $config): bool
    {
        global $resource;

        $db = Database::get();

        $colName     = $resource[$elementId];
        $currentLvl  = (int)($USER[$colName] ?? 0);
        $targetLevel = $currentLvl + 1;

        /**
         * SAFETY-PATCH für laboratory_inter / Labor-Verbund
         *
         * Die BuildFunctions erwarten bei Forschung:
         *   $PLANET[$resource[31].'_inter']
         *
         * In deiner Version existiert dieses Feld nicht.
         * Wir setzen hier einen numerischen Default-Wert,
         * damit getBuildingTime() NICHT versucht, ein Array
         * zu iterieren und keine "Undefined array key" Warnung wirft.
         *
         * Wichtig: $PLANET wird hier nur lokal kopiert übergeben,
         * d.h. das ändert NICHTS dauerhaft in der Datenbank.
         */
        $laborKey = $resource[31] ?? null;
        if ($laborKey !== null) {
            if (!isset($PLANET[$laborKey])) {
                $PLANET[$laborKey] = 0;
            }

            $laborInterKey = $laborKey . '_inter';
            if (!isset($PLANET[$laborInterKey])) {
                // numerischer Dummywert → BuildFunctions nimmt den einfachen Pfad
                $PLANET[$laborInterKey] = 0;
            }
        }

        /**
         * SAFETY-PATCH: fehlendes factor[] Array bei Bot-Usern
         *
         * Spieler besitzen normalerweise:
         *   $USER['factor']['ResearchTime']
         *
         * Bot-User jedoch nicht -> führt zu
         *   Undefined array key "factor"
         *
         * Wir setzen einen neutralen Standardwert (0% Bonus)
         */
        if (!isset($USER['factor']) || !is_array($USER['factor'])) {
            $USER['factor'] = [];
        }

        if (!isset($USER['factor']['ResearchTime'])) {
            $USER['factor']['ResearchTime'] = 0;
        }

        // Kosten & Bauzeit berechnen
        $costResources = BuildFunctions::getElementPrice($USER, $PLANET, $elementId, false, $targetLevel);
        $buildTime     = BuildFunctions::getBuildingTime($USER, $PLANET, $elementId, $costResources);

        /**
         * 5) Ressourcenpuffer prüfen
         *    -> Bots sollen nach dem Start noch Mindestreserven haben
         */
        $metal   = (int)($PLANET[$resource[901]] ?? 0);
        $crystal = (int)($PLANET[$resource[902]] ?? 0);
        $deut    = (int)($PLANET[$resource[903]] ?? 0);

        $costM = (int)($costResources[901] ?? 0);
        $costC = (int)($costResources[902] ?? 0);
        $costD = (int)($costResources[903] ?? 0);

        $leftM = $metal   - $costM;
        $leftC = $crystal - $costC;
        $leftD = $deut    - $costD;

        if ($leftM < self::MIN_METAL || $leftC < self::MIN_CRYSTAL || $leftD < self::MIN_DEUT) {
            self::log([
                'action' => 'RESEARCH_SKIP_LOW_RES',
                'uid'    => $USER['id'],
                'tech'   => $config['label'],
                'lvl'    => $targetLevel,
                'metal'  => $metal,
                'crys'   => $crystal,
                'deut'   => $deut,
                'costM'  => $costM,
                'costC'  => $costC,
                'costD'  => $costD,
                'leftM'  => $leftM,
                'leftC'  => $leftC,
                'leftD'  => $leftD,
            ]);
            return false;
        }

        // Kann der Bot sich das leisten?
        if (!BuildFunctions::isElementBuyable($USER, $PLANET, $elementId, $costResources)) {
            self::log([
                'action' => 'RESEARCH_NO_RES',
                'uid'    => $USER['id'],
                'tech'   => $config['label'],
                'lvl'    => $targetLevel,
                'cost'   => $costResources,
                'metal'  => $metal,
                'crys'   => $crystal,
                'deut'   => $deut,
            ]);
            return false;
        }

        $now     = TIMESTAMP;
        $endTime = $now + max(1, (int)$buildTime);

        // Ressourcen lokal abziehen
        if ($costM > 0) {
            $PLANET[$resource[901]] -= $costM;
        }
        if ($costC > 0) {
            $PLANET[$resource[902]] -= $costC;
        }
        if ($costD > 0) {
            $PLANET[$resource[903]] -= $costD;
        }
        if (isset($costResources[921])) {
            $USER[$resource[921]] -= $costResources[921];
        }

        // Bestehende Queue holen
        $queue = [];
        if (!empty($USER['b_tech_queue'])) {
            $tmp = @unserialize($USER['b_tech_queue']);
            if (is_array($tmp)) {
                $queue = $tmp;
            }
        }

        // Eintrag anhängen
        $queue[] = [
            $elementId,         // 0: Tech-ID
            $targetLevel,       // 1: Ziel-Level
            $buildTime,         // 2: Bauzeit
            $endTime,           // 3: Fertigstellung
            $PLANET['id'],      // 4: Planet-ID
        ];

        // User-Queue-Felder setzen
        $USER['b_tech']        = $endTime;
        $USER['b_tech_id']     = $elementId;
        $USER['b_tech_planet'] = $PLANET['id'];
        $USER['b_tech_queue']  = serialize($queue);

        // In DB schreiben (User + Planet)
        $db->update(
            'UPDATE %%USERS%% u, %%PLANETS%% p
             SET
                u.b_tech        = :bTech,
                u.b_tech_id     = :bTechId,
                u.b_tech_planet = :bTechPlanet,
                u.b_tech_queue  = :bTechQueue,
                p.metal         = :metal,
                p.crystal       = :crystal,
                p.deuterium     = :deuterium
             WHERE u.id = :uid
               AND p.id = :pid;',
            [
                ':bTech'       => $USER['b_tech'],
                ':bTechId'     => $USER['b_tech_id'],
                ':bTechPlanet' => $USER['b_tech_planet'],
                ':bTechQueue'  => $USER['b_tech_queue'],
                ':metal'       => $PLANET[$resource[901]],
                ':crystal'     => $PLANET[$resource[902]],
                ':deuterium'   => $PLANET[$resource[903]],
                ':uid'         => $USER['id'],
                ':pid'         => $PLANET['id'],
            ]
        );

        self::log([
            'action' => 'RESEARCH_START',
            'uid'    => $USER['id'],
            'tech'   => $config['label'],
            'id'     => $elementId,
            'from'   => $currentLvl,
            'to'     => $targetLevel,
            'time'   => $buildTime,
            'end'    => $endTime,
        ]);

        return true;
    }

    /* =========================
     * DEBUG LOG
     * ========================= */
    private static function log(array $data): void
    {
        $dir = ROOT_PATH . 'includes/ai_log/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data['time']     = time();
        $data['datetime'] = date('Y-m-d H:i:s');

        file_put_contents(
            $dir . 'bot_research.json',
            json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );
    }
}
