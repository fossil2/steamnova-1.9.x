<?php

/**
 *  2Moons
 *   by Jan-Otto Kröpke 2009-2016
 *
 * For the full copyright and license information, please view the LICENSE
 *
 * @package 2Moons
 * @author Jan-Otto Kröpke <slaver7@gmail.com>
 * @copyright 2009 Lucky
 * @copyright 2016 Jan-Otto Kröpke <koraykarakus@yahoo.com>
 * @licence MIT
 * @version 1.8.x
 * @link https://github.com/jkroepke/2Moons
 */

function calculateMIPAttack(
	$TargetDefTech,
	$OwnerAttTech,
	$missiles,
	$targetDefensive,
	$firstTarget,
	$defenseMissles
)
{
	global $pricelist, $CombatCaps;

	$destroyShips = array();

	$TargetDefTech  = (int)$TargetDefTech;
	$OwnerAttTech   = (int)$OwnerAttTech;
	$missiles       = (int)$missiles;
	$firstTarget    = (int)$firstTarget;
	$defenseMissles = (int)$defenseMissles;

	/* =========================
	   ABFANGRAKETEN
	   ========================= */

	$countMissles = max(
		0,
		$missiles - $defenseMissles
	);

	if ($countMissles <= 0) {
		return $destroyShips;
	}


	/* =========================
	   RAKETENSCHADEN
	   ========================= */

	if (!isset($CombatCaps[503]['attack'])) {
		return $destroyShips;
	}

	$totalAttack =
		$countMissles
		* (float)$CombatCaps[503]['attack']
		* (1 + 0.1 * $OwnerAttTech);




/* =========================
   PRIMÄRZIEL
   ========================= */

if ($firstTarget > 0) {

    // Gewähltes Ziel existiert nicht mehr / nicht vorhanden
    if (
        !isset($targetDefensive[$firstTarget])
        || (int)$targetDefensive[$firstTarget] <= 0
    ) {
        return $destroyShips;
    }

    // Nur das ausgewählte Ziel angreifen
    $targetDefensive = array(
        $firstTarget => $targetDefensive[$firstTarget]
    );
}


	/* =========================
	   VERTEIDIGUNG ZERSTÖREN
	   ========================= */

	foreach ($targetDefensive as $element => $count)
	{
		$element = (int)$element;
		$count   = (int)$count;

		if ($element <= 0) {
			throw new Exception(
				"Unknown error. Please report this error on tracker.2moons.cc. Debuginforations:<br><br>"
				. serialize(
					array(
						$TargetDefTech,
						$OwnerAttTech,
						$missiles,
						$targetDefensive,
						$firstTarget,
						$defenseMissles
					)
				)
			);
		}

		if ($count <= 0) {
			continue;
		}


		/* =========================
		   KOSTEN DER VERTEIDIGUNG
		   ========================= */

		$metalCost =
			(float)($pricelist[$element]['cost'][901] ?? 0);

		$crystalCost =
			(float)($pricelist[$element]['cost'][902] ?? 0);


		/* =========================
		   STRUKTURWERT
		   ========================= */

		$elementStructurePoints =
			($metalCost + $crystalCost)
			* (1 + 0.1 * $TargetDefTech)
			/ 10;


		/*
		 * Mindesthaltbarkeit für billige Verteidigung.
		 *
		 * Kleine Anlagen sollen nicht mehr in extrem großen
		 * Mengen durch eine einzelne Rakete zerstört werden.
		 *
		 * Bei 12.000 Angriff:
		 * 12.000 / 1.500 = maximal ca. 8 Stück.
		 *
		 * Teure Verteidigung wie Plasmawerfer verwendet
		 * weiterhin ihren höheren normalen Strukturwert.
		 */
		$MIN_DEFENSE_STRUCTURE = 3000;

		$elementStructurePoints = max(
			$elementStructurePoints,
			$MIN_DEFENSE_STRUCTURE
		);


		if ($elementStructurePoints <= 0) {
			continue;
		}


		/* =========================
		   ZERSTÖRTE ANZAHL
		   ========================= */

		$destroyCount =
			(int)floor(
				$totalAttack / $elementStructurePoints
			);

		$destroyCount =
			min(
				$destroyCount,
				$count
			);


		if ($destroyCount > 0) {

			$totalAttack -=
				$destroyCount
				* $elementStructurePoints;

			$destroyShips[$element] =
				$destroyCount;
		}


		if ($totalAttack <= 0) {
			break;
		}
	}

	return $destroyShips;
}