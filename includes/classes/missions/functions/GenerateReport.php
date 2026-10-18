<?php

function GenerateReport($combatResult, $reportInfo)
{
    $DATA = [];

    // =====================================================
    // BASIC INFO
    // =====================================================

    $DATA['mode'] = (int)($reportInfo['moonDestroy'] ?? 0);
    $DATA['time'] = $reportInfo['thisFleet']['fleet_start_time'] ?? TIMESTAMP;

    $DATA['start'] = [
        $reportInfo['thisFleet']['fleet_start_galaxy'] ?? 0,
        $reportInfo['thisFleet']['fleet_start_system'] ?? 0,
        $reportInfo['thisFleet']['fleet_start_planet'] ?? 0,
        $reportInfo['thisFleet']['fleet_start_type'] ?? 0,
    ];

    $DATA['koords'] = [
        $reportInfo['thisFleet']['fleet_end_galaxy'] ?? 0,
        $reportInfo['thisFleet']['fleet_end_system'] ?? 0,
        $reportInfo['thisFleet']['fleet_end_planet'] ?? 0,
        $reportInfo['thisFleet']['fleet_end_type'] ?? 0,
    ];

    $DATA['units'] = [
        (int)($combatResult['unitLost']['attacker'] ?? 0),
        (int)($combatResult['unitLost']['defender'] ?? 0),
    ];

    $DATA['debris'] = $reportInfo['debris'] ?? [
        901 => 0,
        902 => 0,
    ];

    $DATA['steal'] = $reportInfo['stealResource'] ?? [
        901 => 0,
        902 => 0,
        903 => 0,
    ];

    $DATA['result'] = $combatResult['won'] ?? 'w';

    $DATA['moon'] = [
        'moonName'            => $reportInfo['moonName'] ?? null,
        'moonChance'          => (int)($reportInfo['moonChance'] ?? 0),
        'moonCreated'         => !empty($reportInfo['moonCreated']),
        'moonDestroyChance'   => (int)($reportInfo['moonDestroyChance'] ?? 0),
        'moonDestroySuccess'  => (int)($reportInfo['moonDestroySuccess'] ?? 0),
        'fleetDestroyChance'  => (int)($reportInfo['fleetDestroyChance'] ?? 0),
        'fleetDestroySuccess' => (int)($reportInfo['fleetDestroySuccess'] ?? 0),
    ];

    // =====================================================
    // START UNITS
    // =====================================================

    if (isset($combatResult['startUnits']) && is_array($combatResult['startUnits'])) {
        $DATA['startUnits'] = $combatResult['startUnits'];
    }

    // =====================================================
    // PLAYERS
    // ===================================================


     $DATA['attackers'] = [];
     $DATA['defenders'] = [];
     $DATA['players']   = [];

    foreach (['attacker', 'defender'] as $side) {
    if (empty($combatResult[$side]) || !is_array($combatResult[$side])) {
        continue;
    }

    foreach ($combatResult[$side] as $fleetIndex => $fleet) {
        if (!isset($fleet['player']) || !is_array($fleet['player'])) {
            continue;
        }

        $username = $fleet['player']['username'] ?? '';
        if ($username === '') {
            continue;
        }

        $userId = isset($fleet['player']['id']) ? (int)$fleet['player']['id'] : -1;

       
        if ($userId === 0) {
            $playerKey = 'npc_' . $side . '_' . $fleetIndex;
        } else {
            $playerKey = (string)$userId;
        }

        $DATA['players'][$playerKey] = [
            'name' => $username,
            'koords' => [
                $fleet['fleetDetail']['fleet_start_galaxy'] ?? 0,
                $fleet['fleetDetail']['fleet_start_system'] ?? 0,
                $fleet['fleetDetail']['fleet_start_planet'] ?? 0,
                $fleet['fleetDetail']['fleet_start_type'] ?? 0,
            ],
            'tech' => [
                ((int)($fleet['player']['military_tech'] ?? 0)) * 10,
                ((int)($fleet['player']['shield_tech'] ?? 0)) * 10,
                ((int)($fleet['player']['defence_tech'] ?? 0)) * 10,
            ],
        ];

        if ($side === 'attacker') {
            $DATA['attackers'][$playerKey] = $username;
        } else {
            $DATA['defenders'][$playerKey] = $username;
        }
      }
   }
     
    // =====================================================
    // ROUNDS (BattleV3 native format)
    // =====================================================

    $DATA['rounds'] = [];

    if (!empty($combatResult['rw']) && is_array($combatResult['rw'])) {
        foreach ($combatResult['rw'] as $roundNumber => $roundData) {
            $DATA['rounds'][(int)$roundNumber] = [
                'attacker_before' => $roundData['attacker_before'] ?? [],
                'defender_before' => $roundData['defender_before'] ?? [],
                'attacker_after'  => $roundData['attacker_after'] ?? [],
                'defender_after'  => $roundData['defender_after'] ?? [],
            ];
        }
    }

    // =====================================================
    // REBUILD / EXTRA
    // =====================================================

    
    $DATA['rebuild'] = $combatResult['repair'] ?? [];
    $DATA['additionalInfo'] = $reportInfo['additionalInfo'] ?? '';

    return $DATA;
}
