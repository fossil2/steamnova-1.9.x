<?php
declare(strict_types=1);

/**
 * BattleV3
 *
 * - 5 Kampfrunden
 * - Unentschieden möglich
 * - Rapidfire aus SQL-Tabelle uni1_vars_rapidfirev3
 * - RF-Cap standardmäßig 6
 * - Defense-Rebuild optional
 * - Persistenter Hüllenschaden
 *
 * Erwartet globale Arrays:
 *   $CombatCaps
 *   $pricelist
 *   $reslist
 *
 * Erwartet 2Moons-Database-Objekt für Loader:
 *   loadRapidfireTable($db, 'uni1_vars_rapidfirev3');
 */
final class BattleV3
{
    private array $cfg;

    public function __construct(array $cfg = [])
    {
        $this->cfg = $cfg + [
            'max_rounds'             => 5,
            'fleet_debris_rate'      => 0.30,
            'def_debris_rate'        => 0.00,
            'explosion_limit'        => 0.70,
            'defense_rebuild_chance' => 0.70,
            'use_defense_rebuild'    => true,
            'rapidfire_cap'          => 6,
        ];
    }

    public function simulate(array $attackers, array $defenders): array
    {
        $initialAttackers = $this->deepCopySide($attackers);
        $initialDefenders = $this->deepCopySide($defenders);

        $attackerState = $this->buildSideState($attackers);
        $defenderState = $this->buildSideState($defenders);

        $roundReports = [];

        for ($round = 1; $round <= (int)$this->cfg['max_rounds']; $round++) {
            if ($this->sideIsDead($attackerState) || $this->sideIsDead($defenderState)) {
                break;
            }

            $this->resetRoundShields($attackerState);
            $this->resetRoundShields($defenderState);

            $roundReports[$round] = [
                'attacker_before' => $this->exportCounts($attackerState),
                'defender_before' => $this->exportCounts($defenderState),
            ];

            $attackerCanFire = !$this->sideIsDead($attackerState);
            $defenderCanFire = !$this->sideIsDead($defenderState);

            $attackerFireState = $this->copyState($attackerState);
            $defenderFireState = $this->copyState($defenderState);

            if ($attackerCanFire) {
                $this->fireSide($attackerFireState, $defenderState);
            }

            if ($defenderCanFire) {
                $this->fireSide($defenderFireState, $attackerState);
            }

            $this->cleanupState($attackerState);
            $this->cleanupState($defenderState);

            $roundReports[$round]['attacker_after'] = $this->exportCounts($attackerState);
            $roundReports[$round]['defender_after'] = $this->exportCounts($defenderState);
        }

        $attackersAfter = $this->applyStateToFleets($attackers, $attackerState);
        $defendersAfterBeforeRepair = $this->applyStateToFleets($defenders, $defenderState);

        $won = $this->resolveWinner($attackersAfter, $defendersAfterBeforeRepair);

        $lostAttackersByType = $this->computeLossByType($initialAttackers, $attackersAfter);
        $lostDefendersByType = $this->computeLossByType($initialDefenders, $defendersAfterBeforeRepair);

        $defendersAfterRepair = $defendersAfterBeforeRepair;
        $repairedDefense = [];

        if ((bool)$this->cfg['use_defense_rebuild']) {
            $repairedDefense = $this->computeRepairedDefense($lostDefendersByType);
            $defendersAfterRepair = $this->applyRepairedDefense($defendersAfterRepair, $repairedDefense);
        }

        return [
            'won'        => $won,
            'attacker'   => $attackersAfter,
            'defender'   => $defendersAfterRepair,
            'unitLost'   => [
                'attacker' => array_sum($lostAttackersByType),
                'defender' => array_sum($lostDefendersByType),
            ],
            'debris'     => [
                'attacker' => $this->computeDebrisFromLoss($lostAttackersByType),
                'defender' => $this->computeDebrisFromLoss($lostDefendersByType),
            ],
            'repair'     => $repairedDefense,
            'rw'         => $roundReports,
            'startUnits' => [
                'attacker' => $this->extractStartUnits($initialAttackers),
                'defender' => $this->extractStartUnits($initialDefenders),
            ],
        ];
    }

    private function buildSideState(array $sideFleets): array
    {
        global $CombatCaps, $pricelist, $reslist;

        $defenseSet = [];
        foreach (($reslist['defense'] ?? []) as $defenseId) {
            $defenseSet[(int)$defenseId] = true;
        }

        $state = [];

        foreach ($sideFleets as $fleetId => $fleet) {
            $player = $fleet['player'] ?? [];
            $factor = $player['factor'] ?? [];

            $weaponTech = (int)($player['military_tech'] ?? 0);
            $shieldTech = (int)($player['shield_tech'] ?? 0);
            $armourTech = (int)($player['defence_tech'] ?? 0);

            $attackFactor = (float)($factor['attack'] ?? 1.0);
            $shieldFactor = (float)($factor['shield'] ?? 1.0);
            $defenseFactor = (float)($factor['defense'] ?? 1.0);

            foreach (($fleet['unit'] ?? []) as $elementId => $count) {
                $elementId = (int)$elementId;
                $count = (int)$count;

                if ($count <= 0 || !isset($CombatCaps[$elementId])) {
                    continue;
                }

                $caps = $CombatCaps[$elementId];

                $baseAttack = (float)($caps['attack'] ?? 0.0);
                $baseShield = (float)($caps['shield'] ?? 0.0);

                $metal    = (float)($pricelist[$elementId]['cost'][901] ?? 0.0);
                $crystal  = (float)($pricelist[$elementId]['cost'][902] ?? 0.0);
                $baseHull = ($metal + $crystal) / 10.0;

                $attack = $baseAttack * (1.0 + (0.1 * $weaponTech)) * $attackFactor;
                $shield = $baseShield * (1.0 + (0.1 * $shieldTech)) * $shieldFactor;
                $hull   = $baseHull * (1.0 + (0.1 * $armourTech)) * $defenseFactor;

                $rapidfire = [];
                if (!empty($caps['sd']) && is_array($caps['sd'])) {
                    foreach ($caps['sd'] as $targetId => $rfValue) {
                        $rapidfire[(int)$targetId] = min(
                            (int)$this->cfg['rapidfire_cap'],
                            max(1, (int)$rfValue)
                        );
                    }
                }

                $state[$fleetId . ':' . $elementId] = [
                    'fleetId'        => (int)$fleetId,
                    'elementId'      => $elementId,
                    'isDefense'      => isset($defenseSet[$elementId]),
                    'count'          => $count,
                    'attack'         => $attack,
                    'shield_max'     => $shield,
                    'shield_current' => $shield,
                    'hull_max'       => $hull,
                    'hull_current'   => $hull,
                    'rapidfire'      => $rapidfire,
                ];
            }
        }

        return $state;
    }

    private function fireSide(array &$attackSide, array &$defendSide): void
    {
        if ($this->sideIsDead($attackSide)) {
            return;
        }

        foreach (array_keys($attackSide) as $attackerKey) {
            if (!isset($attackSide[$attackerKey])) {
                continue;
            }

            if ((int)$attackSide[$attackerKey]['count'] <= 0) {
                continue;
            }

            $shots = (int)$attackSide[$attackerKey]['count'];

            for ($i = 0; $i < $shots; $i++) {
                if ($this->sideIsDead($defendSide)) {
                    return;
                }

                $continueRapidfire = true;

                while ($continueRapidfire) {
                    if ($this->sideIsDead($defendSide)) {
                        return;
                    }

                    $targetKey = $this->pickRandomTargetKey($defendSide);
                    if ($targetKey === null || !isset($defendSide[$targetKey])) {
                        return;
                    }

                    $targetElementId = (int)$defendSide[$targetKey]['elementId'];
                    $shooterAttack   = (float)$attackSide[$attackerKey]['attack'];

                    $this->applySingleShot($shooterAttack, $defendSide[$targetKey]);

                    if ((int)$defendSide[$targetKey]['count'] <= 0) {
                        unset($defendSide[$targetKey]);
                    }

                    $rfValue = (int)($attackSide[$attackerKey]['rapidfire'][$targetElementId] ?? 1);
                    $continueRapidfire = $this->rollRapidfire($rfValue);
                }
            }
        }
    }

    private function applySingleShot(float $attackPower, array &$target): void
    {
        if ((int)$target['count'] <= 0 || $attackPower <= 0.0) {
            return;
        }

        $shieldCurrent = (float)$target['shield_current'];
        $shieldMax     = (float)$target['shield_max'];
        $hullCurrent   = (float)($target['hull_current'] ?? $target['hull_max']);
        $hullMax       = (float)$target['hull_max'];

        if ($hullMax <= 0.0) {
            return;
        }

        $remainingDamage = $attackPower;

        if ($shieldCurrent > 0.0) {
            if ($remainingDamage <= $shieldCurrent) {
                $target['shield_current'] -= $remainingDamage;
                return;
            }

            $remainingDamage -= $shieldCurrent;
            $target['shield_current'] = 0.0;
        }

        $hullCurrent -= $remainingDamage;
        $target['hull_current'] = $hullCurrent;

        if ($hullCurrent <= 0.0) {
            $target['count']--;

            if ((int)$target['count'] > 0) {
                $target['hull_current'] = $hullMax;
                $target['shield_current'] = $shieldMax;
            }

            return;
        }

        $remainingHullRatio = $hullCurrent / $hullMax;

        if ($remainingHullRatio < (float)$this->cfg['explosion_limit']) {
            $explodeChance = 1.0 - $remainingHullRatio;

            if ($this->rollExplosion($explodeChance)) {
                $target['count']--;

                if ((int)$target['count'] > 0) {
                    $target['hull_current'] = $hullMax;
                    $target['shield_current'] = $shieldMax;
                }
            }
        }
    }

    private function rollRapidfire(int $rfValue): bool
    {
        if ($rfValue <= 1) {
            return false;
        }

        $chance = ($rfValue - 1) / $rfValue;
        return (mt_rand(1, 1000000) / 1000000) < $chance;
    }

    private function rollExplosion(float $chance): bool
    {
        $chance = max(0.0, min(1.0, $chance));
        return (mt_rand(1, 1000000) / 1000000) < $chance;
    }

    private function pickRandomTargetKey(array $state): ?string
    {
        $weighted = [];

        foreach ($state as $key => $unit) {
            $count = (int)($unit['count'] ?? 0);
            if ($count > 0) {
                $weighted[$key] = $count;
            }
        }

        if ($weighted === []) {
            return null;
        }

        $sum = array_sum($weighted);
        $roll = random_int(1, $sum);

        foreach ($weighted as $key => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return (string)$key;
            }
        }

        return (string)array_key_first($weighted);
    }

    private function resetRoundShields(array &$state): void
    {
        foreach ($state as &$unit) {
            if ((int)$unit['count'] > 0) {
                $unit['shield_current'] = $unit['shield_max'];
            }
        }
        unset($unit);
    }

    private function cleanupState(array &$state): void
    {
        foreach ($state as $key => $unit) {
            if ((int)$unit['count'] <= 0) {
                unset($state[$key]);
            }
        }
    }

    private function exportCounts(array $state): array
    {
        $result = [];

        foreach ($state as $unit) {
            $count = (int)($unit['count'] ?? 0);
            if ($count <= 0) {
                continue;
            }

            $fleetId   = (int)$unit['fleetId'];
            $elementId = (int)$unit['elementId'];

            if (!isset($result[$fleetId])) {
                $result[$fleetId] = [];
            }

            $result[$fleetId][$elementId] = $count;
        }

        return $result;
    }

    private function resolveWinner(array $attackers, array $defenders): string
    {
        $attackerDead = $this->isDead($attackers);
        $defenderDead = $this->isDead($defenders);

        if ($attackerDead && !$defenderDead) {
            return 'r';
        }

        if ($defenderDead && !$attackerDead) {
            return 'a';
        }

        return 'w';
    }

    private function applyStateToFleets(array $fleets, array $state): array
    {
        foreach ($fleets as &$fleet) {
            $fleet['unit'] = [];
        }
        unset($fleet);

        foreach ($state as $unit) {
            $count = (int)($unit['count'] ?? 0);
            if ($count <= 0) {
                continue;
            }

            $fleetId   = (int)$unit['fleetId'];
            $elementId = (int)$unit['elementId'];

            if (!isset($fleets[$fleetId])) {
                continue;
            }

            $fleets[$fleetId]['unit'][$elementId] =
                ($fleets[$fleetId]['unit'][$elementId] ?? 0) + $count;
        }

        return $fleets;
    }

    private function computeLossByType(array $before, array $after): array
    {
        $lost = [];

        foreach ($before as $fleetId => $fleet) {
            foreach (($fleet['unit'] ?? []) as $elementId => $beforeCount) {
                $beforeCount = (int)$beforeCount;
                $afterCount  = (int)($after[$fleetId]['unit'][$elementId] ?? 0);
                $diff = $beforeCount - $afterCount;

                if ($diff > 0) {
                    $lost[(int)$elementId] = ($lost[(int)$elementId] ?? 0) + $diff;
                }
            }
        }

        return $lost;
    }

    private function computeDebrisFromLoss(array $lostByType): array
    {
        global $pricelist, $reslist;

        $defenseSet = [];
        foreach (($reslist['defense'] ?? []) as $defenseId) {
            $defenseSet[(int)$defenseId] = true;
        }

        $metal = 0.0;
        $crystal = 0.0;

        foreach ($lostByType as $elementId => $lostCount) {
            $elementId = (int)$elementId;
            $lostCount = (int)$lostCount;

            $metalCost   = (float)($pricelist[$elementId]['cost'][901] ?? 0.0);
            $crystalCost = (float)($pricelist[$elementId]['cost'][902] ?? 0.0);

            $rate = isset($defenseSet[$elementId])
                ? (float)$this->cfg['def_debris_rate']
                : (float)$this->cfg['fleet_debris_rate'];

            $metal   += $metalCost * $lostCount * $rate;
            $crystal += $crystalCost * $lostCount * $rate;
        }

        return [
            901 => (int)floor($metal),
            902 => (int)floor($crystal),
        ];
    }

    private function computeRepairedDefense(array $lostByType): array
    {
        global $reslist;

        $defenseSet = [];
        foreach (($reslist['defense'] ?? []) as $defenseId) {
            $defenseSet[(int)$defenseId] = true;
        }

        $repaired = [];

        foreach ($lostByType as $elementId => $lostCount) {
            $elementId = (int)$elementId;
            $lostCount = (int)$lostCount;

            if ($lostCount <= 0 || !isset($defenseSet[$elementId])) {
                continue;
            }

            $restore = 0;
            $chance = (float)$this->cfg['defense_rebuild_chance'];

            for ($i = 0; $i < $lostCount; $i++) {
                if ((mt_rand(1, 1000000) / 1000000) < $chance) {
                    $restore++;
                }
            }

            if ($restore > 0) {
                $repaired[$elementId] = $restore;
            }
        }

        return $repaired;
    }

    private function applyRepairedDefense(array $defenders, array $repairedDefense): array
    {
        if ($repairedDefense === []) {
            return $defenders;
        }

        if (!isset($defenders[0])) {
            return $defenders;
        }

        foreach ($repairedDefense as $elementId => $count) {
            $defenders[0]['unit'][(int)$elementId] =
                ($defenders[0]['unit'][(int)$elementId] ?? 0) + (int)$count;
        }

        return $defenders;
    }

    private function extractStartUnits(array $side): array
    {
        $result = [];

        foreach ($side as $fleetId => $fleet) {
            $result[$fleetId] = [
                'unit' => $fleet['unit'] ?? [],
            ];
        }

        return $result;
    }

    private function sideIsDead(array $state): bool
    {
        foreach ($state as $unit) {
            if ((int)($unit['count'] ?? 0) > 0) {
                return false;
            }
        }

        return true;
    }

    private function isDead(array $side): bool
    {
        foreach ($side as $fleet) {
            foreach (($fleet['unit'] ?? []) as $count) {
                if ((int)$count > 0) {
                    return false;
                }
            }
        }

        return true;
    }

    private function deepCopySide(array $side): array
    {
        $copy = [];

        foreach ($side as $fleetId => $fleet) {
            $copy[$fleetId] = [
                'player' => $fleet['player'] ?? [],
                'unit'   => $fleet['unit'] ?? [],
            ] + $fleet;
        }

        return $copy;
    }

    private function copyState(array $state): array
    {
        $copy = [];

        foreach ($state as $key => $unit) {
            $copy[$key] = $unit;
        }

        return $copy;
    }
}

/**
 * Lädt Rapidfire aus SQL in $CombatCaps[$elementID]['sd'][$rapidfireID]
 *
 * SQL-Tabelle:
 *   uni1_vars_rapidfirev3
 * Felder:
 *   elementID
 *   rapidfireID
 *   shoots
 */
function loadRapidfireTable($db, string $tableName = 'uni1_vars_rapidfirev3', int $rapidfireCap = 6): void
{
    global $CombatCaps;

    foreach ($CombatCaps as $elementId => $caps) {
        if (isset($CombatCaps[$elementId]['sd'])) {
            unset($CombatCaps[$elementId]['sd']);
        }
    }

    $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
    if ($safeTable === '') {
        throw new RuntimeException('Ungültiger Tabellenname für Rapidfire.');
    }

    $rapidfireCap = max(1, $rapidfireCap);

    $sql = "SELECT `elementID`, `rapidfireID`, `shoots` FROM `".$safeTable."`;";
    $result = $db->select($sql);

    foreach ($result as $row) {
        $attacker = (int)($row['elementID'] ?? 0);
        $target   = (int)($row['rapidfireID'] ?? 0);
        $value    = min($rapidfireCap, max(1, (int)($row['shoots'] ?? 1)));

        if ($attacker <= 0 || $target <= 0) {
            continue;
        }

        if (!isset($CombatCaps[$attacker]) || !is_array($CombatCaps[$attacker])) {
            $CombatCaps[$attacker] = [];
        }

        if (!isset($CombatCaps[$attacker]['sd']) || !is_array($CombatCaps[$attacker]['sd'])) {
            $CombatCaps[$attacker]['sd'] = [];
        }

        $CombatCaps[$attacker]['sd'][$target] = $value;
    }
}