<?php
declare(strict_types=1);

require_once __DIR__ . '/BattleV3.php';

function calculateAttack(
    array &$fleetAttack,
    array &$fleetDefend,
    float $fleetIntoDebris,
    float $defIntoDebris,
    int $universe
): array
{
    $db = Database::get();

    loadRapidfireTable(
        $db,
        'uni' . $universe . '_vars_rapidfirev3',
        6
    );

    $startUnits = [
        'attacker' => $fleetAttack,
        'defender' => $fleetDefend,
    ];

    $engine = new BattleV3([
        'max_rounds'             => 5,
        'fleet_debris_rate'      => $fleetIntoDebris / 100,
        'def_debris_rate'        => $defIntoDebris / 100,
        'explosion_limit'        => 0.50,
        'defense_rebuild_chance' => 0.70,
        'use_defense_rebuild'    => true,
        'rapidfire_cap'          => 6,
    ]);

    $combatResult = $engine->simulate($fleetAttack, $fleetDefend);

    $fleetAttack = $combatResult['attacker'];
    $fleetDefend = $combatResult['defender'];

    $combatResult['startUnits'] = $startUnits;
    $combatResult['endUnitsBeforeRebuild'] = [
        'attacker' => $combatResult['attacker'],
        'defender' => $combatResult['defender'],
    ];

    if (!isset($combatResult['rebuild']) && isset($combatResult['repair'])) {
        $combatResult['rebuild'] = $combatResult['repair'];
    }

    return $combatResult;
}