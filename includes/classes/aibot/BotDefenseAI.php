<?php
declare(strict_types=1);

class BotDefenseAI
{
    private const INTERCEPTOR = 502;
    private const INTERCEPTOR_TARGET = 10;
    private const RESOURCE_SHARE = 0.10;
    private const MAX_DEFENSE_BATCH = 3;
    private const MAX_MISSILE_BATCH = 2;

    public static function run(int $userId): void
    {
        global $resource, $reslist;
        $db = Database::get();
        $user = $db->selectSingle(
            'SELECT * FROM %%USERS%% WHERE id = :uid AND is_bot = 1;',
            [':uid' => $userId]
        );
        if (!$user || !empty($user['urlaubs_modus'])) {
            return;
        }
        // Gleiche Hauptplanet-Auswahl wie BotBuildAI und BotColonyBuildAI.
        $planet = $db->selectSingle(
            'SELECT * FROM %%PLANETS%% WHERE id_owner = :uid
             AND planet_type = 1 ORDER BY id ASC LIMIT 1;',
            [':uid' => $userId]
        );
        if (!$planet || (int)($planet['hangar'] ?? 0) < 2) {
            return;
        }
        // Bestehende Flotten-/Verteidigungsauftraege niemals ersetzen.
        $oldQueue = (string)($planet['b_hangar_id'] ?? '');
        if ($oldQueue !== '') {
            $queue = @unserialize($oldQueue, ['allowed_classes' => false]);
            if (!is_array($queue) || $queue !== []) {
                return;
            }
        }
        // Werft/Naniten im Bau: Werftauftraege bis zum Abschluss aussetzen.
        $buildingQueue = (string)($planet['b_building_id'] ?? '');
        if ($buildingQueue !== '') {
            $buildings = @unserialize($buildingQueue, ['allowed_classes' => false]);
            if (!is_array($buildings)) {
                return;
            }
            foreach ($buildings as $entry) {
                if (!is_array($entry) || !isset($entry[0])) {
                    return;
                }
                if (in_array((int)$entry[0], [21, 15], true)) {
                    return;
                }
            }
        }
        $score = $db->selectSingle(
            'SELECT total_points FROM %%USER_POINTS%%
             WHERE id_owner = :uid AND universe = :uni;',
            [':uid' => $userId, ':uni' => (int)($user['universe'] ?? 1)]
        );
        $targets = self::getTargets($planet, (int)($score['total_points'] ?? 0));
        foreach ($targets as $id => $target) {
            $allowedIds = array_merge($reslist['defense'] ?? [], $reslist['missile'] ?? []);
            if (!isset($resource[$id]) || !in_array($id, $allowedIds)
                || !self::isAccessible($user, $planet, $id)) {
                continue;
            }
            $missing = $target - (int)($planet[$resource[$id]] ?? 0);
            if ($missing <= 0) {
                continue;
            }
            $amount = min($missing, $id === self::INTERCEPTOR
                ? self::MAX_MISSILE_BATCH : self::MAX_DEFENSE_BATCH);
            $amount = min($amount, max(0, (int)Config::get()->max_fleet_per_build));
            if ($id === self::INTERCEPTOR) {
                if (!isset($resource[44], $resource[502], $resource[503])
                    || (int)($planet[$resource[44]] ?? 0) < 2) {
                    continue;
                }
                $missiles = [
                    502 => (int)($planet[$resource[502]] ?? 0),
                    503 => (int)($planet[$resource[503]] ?? 0),
                ];
                $space = BuildFunctions::getMaxConstructibleRockets($user, $planet, $missiles);
                $amount = min($amount, max(0, (int)($space[502] ?? 0)));
            }
            // Mengenweise pruefen; Preisfunktion erhaelt die NEUE Menge.
            for (; $amount > 0; --$amount) {
                $cost = BuildFunctions::getElementPrice($user, $planet, $id, false, $amount);
                if (!self::withinBudget($planet, $cost)) {
                    continue;
                }
                $m = (float)($cost[901] ?? 0);
                $c = (float)($cost[902] ?? 0);
                $d = (float)($cost[903] ?? 0);
                // Ressourcen und Queue gemeinsam schreiben. Vergleichsschutz
                // verhindert das Ueberschreiben zwischenzeitlicher Auftraege.
                $db->update(
                    'UPDATE %%PLANETS%% SET metal = metal - :m,
                     crystal = crystal - :c, deuterium = deuterium - :d,
                     b_hangar_id = :queue, b_hangar = 0
                     WHERE id = :pid AND id_owner = :uid
                       AND b_hangar_id = :oldQueue
                       AND b_building_id = :oldBuildings
                       AND metal >= :minM AND crystal >= :minC
                       AND deuterium >= :minD;',
                    [':m' => $m, ':c' => $c, ':d' => $d,
                     ':queue' => serialize([[$id, $amount]]),
                     ':pid' => (int)$planet['id'], ':uid' => $userId,
                     ':oldQueue' => $oldQueue, ':oldBuildings' => $buildingQueue,
                     ':minM' => $m / self::RESOURCE_SHARE,
                     ':minC' => $c / self::RESOURCE_SHARE,
                     ':minD' => $d / self::RESOURCE_SHARE]
                );
                return; // Hoechstens ein Auftrag pro Durchlauf.
            }
        }
    }

    private static function getTargets(array $planet, int $points): array
    {
        $metal = (int)($planet['metal_mine'] ?? 0);
        $crystal = (int)($planet['crystal_mine'] ?? 0);
        // Raketen zuerst; harte Obergrenzen auch fuer sehr grosse Bots.
        if ($metal >= 24 && $crystal >= 20 && $points >= 15000) {
            return [502 => 10, 401 => 60, 402 => 40, 403 => 10, 404 => 4];
        }
        if ($metal >= 18 && $crystal >= 14 && $points >= 8000) {
            return [502 => 10, 401 => 40, 402 => 25, 403 => 6, 404 => 2];
        }
        return [502 => 10, 401 => 20, 402 => 10, 403 => $points >= 4000 ? 3 : 0];
    }

    private static function isAccessible(array $user, array $planet, int $id): bool
    {
        global $requeriments, $resource;
        // Die Standardfunktion prueft fehlende Felder nicht: explizit absichern.
        foreach ($requeriments[$id] ?? [] as $requiredId => $level) {
            $field = $resource[$requiredId] ?? null;
            if ($field === null || (float)($user[$field] ?? $planet[$field] ?? 0) < $level) {
                return false;
            }
        }
        return BuildFunctions::isTechnologieAccessible($user, $planet, $id);
    }

    private static function withinBudget(array $planet, array $cost): bool
    {
        global $resource;
        if (!$cost || array_sum($cost) <= 0) {
            return false;
        }
        foreach ($cost as $id => $price) {
            // Keine Premiumwaehrung oder unbekannten Ressourcen ausgeben.
            if (!in_array((int)$id, [901, 902, 903], true)) {
                if ($price != 0) {
                    return false;
                }
                continue;
            }
            $field = $resource[$id] ?? null;
            if ($field === null || !is_finite((float)$price) || $price < 0
                || $price > max(0.0, (float)($planet[$field] ?? 0)) * self::RESOURCE_SHARE) {
                return false;
            }
        }
        return true;
    }
}
