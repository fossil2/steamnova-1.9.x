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
 * @copyright 2016 Jan-Otto Kröpke <slaver7@gmail.com>
 * @licence MIT
 * @version 1.8.x Koray Karakuş <koraykarakus@yahoo.com>
 * @link https://github.com/jkroepke/2Moons
 */
class ShowShipyardPage extends AbstractGamePage
{
	public static $requireModule = 0;
	public static $defaultController = 'show';

	function __construct()
	{
		parent::__construct();
	}


	private function safeUnserializeArray($value): array
	{
		if (empty($value) || !is_string($value)) {
			return [];
		}

		$tmp = @unserialize($value, ['allowed_classes' => false]);

		return is_array($tmp) ? $tmp : [];
	}

	private function CancelAuftr(): bool
	{
		global $USER, $PLANET, $resource;

		$ElementQueue = $this->safeUnserializeArray($PLANET['b_hangar_id']);

		$CancelArray = HTTP::_GP('auftr', []);
		if (!is_array($CancelArray) || empty($ElementQueue)) {
			return false;
		}

		foreach ($CancelArray as $Auftr) {
			$Auftr = (int)$Auftr;

			if (!isset($ElementQueue[$Auftr]) || !is_array($ElementQueue[$Auftr])) {
				continue;
			}

			if ($Auftr === 0) {
				$PLANET['b_hangar'] = 0;
			}

			$Element = (int)($ElementQueue[$Auftr][0] ?? 0);
			$Count   = (int)($ElementQueue[$Auftr][1] ?? 0);

			if ($Element <= 0 || $Count <= 0) {
				unset($ElementQueue[$Auftr]);
				continue;
			}

			$costResources = BuildFunctions::getElementPrice($USER, $PLANET, $Element, false, $Count);

			if (isset($costResources[901])) { $PLANET[$resource[901]] += $costResources[901] * FACTOR_CANCEL_SHIPYARD; }
			if (isset($costResources[902])) { $PLANET[$resource[902]] += $costResources[902] * FACTOR_CANCEL_SHIPYARD; }
			if (isset($costResources[903])) { $PLANET[$resource[903]] += $costResources[903] * FACTOR_CANCEL_SHIPYARD; }
			if (isset($costResources[921])) { $USER[$resource[921]]   += $costResources[921] * FACTOR_CANCEL_SHIPYARD; }

			unset($ElementQueue[$Auftr]);
		}

		if (empty($ElementQueue)) {
			$PLANET['b_hangar_id'] = '';
		} else {
			$PLANET['b_hangar_id'] = serialize(array_values($ElementQueue));
		}

		return true;
	}

	private function BuildAuftr($fmenge): void
	{
		global $USER, $PLANET, $reslist, $resource;

		if (!is_array($fmenge) || empty($fmenge)) {
			return;
		}

		$Missiles = [
			502 => (int)$PLANET[$resource[502]],
			503 => (int)$PLANET[$resource[503]],
		];

		$BuildArray = $this->safeUnserializeArray($PLANET['b_hangar_id']);

		foreach ($fmenge as $Element => $Count) {
			$Element = (int)$Element;

			if (
				$Element <= 0
				|| !in_array($Element, array_merge($reslist['fleet'], $reslist['defense'], $reslist['missile']), true)
				|| !BuildFunctions::isTechnologieAccessible($USER, $PLANET, $Element)
			) {
				continue;
			}

			$Count = is_numeric($Count) ? (int)round((float)$Count) : 0;
			if ($Count <= 0) {
				continue;
			}

			$MaxElements = BuildFunctions::getMaxConstructibleElements($USER, $PLANET, $Element);
			$Count = min($Count, (int)Config::get()->max_fleet_per_build);
			$Count = min($Count, (int)$MaxElements);
			$Count = max($Count, 0);

			if (in_array($Element, $reslist['missile'], true)) {
				$MaxMissiles = BuildFunctions::getMaxConstructibleRockets($USER, $PLANET, $Missiles);
				if (isset($MaxMissiles[$Element])) {
					$Count = min($Count, (int)$MaxMissiles[$Element]);
				}

				$Missiles[$Element] += $Count;

			} elseif (in_array($Element, $reslist['one'], true)) {

				$InBuild = false;
				foreach ($BuildArray as $ElementArray) {
					if (!is_array($ElementArray)) {
						continue;
					}
					if ((int)($ElementArray[0] ?? 0) === $Element) {
						$InBuild = true;
						break;
					}
				}

				if ($InBuild) {
					continue;
				}

				if ($Count !== 0 && (int)$PLANET[$resource[$Element]] === 0) {
					$Count = 1;
				}
			}

			if ($Count <= 0) {
				continue;
			}

			$costResources = BuildFunctions::getElementPrice($USER, $PLANET, $Element, false, $Count);

			if (isset($costResources[901])) { $PLANET[$resource[901]] -= $costResources[901]; }
			if (isset($costResources[902])) { $PLANET[$resource[902]] -= $costResources[902]; }
			if (isset($costResources[903])) { $PLANET[$resource[903]] -= $costResources[903]; }
			if (isset($costResources[921])) { $USER[$resource[921]]   -= $costResources[921]; }

			$BuildArray[] = [$Element, $Count];
			$PLANET['b_hangar_id'] = serialize($BuildArray);
		}
	}

	public function show()
	{
		global $USER, $PLANET, $LNG, $resource, $reslist, $config, $requeriments;

		if ((int)$PLANET[$resource[21]] === 0 && !$config->show_ships_no_shipyard) {
			$this->printMessage($LNG['bd_shipyard_required']);
		}

		$buildTodo = HTTP::_GP('fmenge', []);
		$action    = (string)HTTP::_GP('action', '');

		$NotBuilding = true;
		if (!empty($PLANET['b_building_id'])) {
			$CurrentQueue = $this->safeUnserializeArray($PLANET['b_building_id']);
			foreach ($CurrentQueue as $ElementArray) {
				if (!is_array($ElementArray)) {
					continue;
				}
				$queuedId = (int)($ElementArray[0] ?? 0);
				if ($queuedId === 21 || $queuedId === 15) {
					$NotBuilding = false;
					break;
				}
			}
		}

		$ElementQueue = $this->safeUnserializeArray($PLANET['b_hangar_id']);
		$Count        = count($ElementQueue);

		if ((int)$USER['urlaubs_modus'] === 0 && $NotBuilding === true) {

			if (!empty($buildTodo)) {
				$maxBuildQueue = (int)$config->max_elements_ships;
				if ($maxBuildQueue !== 0 && $Count >= $maxBuildQueue) {
					$this->printMessage(sprintf($LNG['bd_max_builds'], $maxBuildQueue));
				}

				$this->BuildAuftr($buildTodo);
				$ElementQueue = $this->safeUnserializeArray($PLANET['b_hangar_id']);
				$Count        = count($ElementQueue);
			}

			if ($action === 'delete') {
				$this->CancelAuftr();
				$ElementQueue = $this->safeUnserializeArray($PLANET['b_hangar_id']);
				$Count        = count($ElementQueue);
			}
		}

		$elementInQueue = [];
		$buildList      = [];
		$elementList    = [];

		if (!empty($ElementQueue)) {
			$Shipyard  = [];
			$QueueTime = 0;

			foreach ($ElementQueue as $Element) {
				if (empty($Element) || !is_array($Element)) {
					continue;
				}

				$elementId = (int)($Element[0] ?? 0);
				$amount    = (int)($Element[1] ?? 0);

				if ($elementId <= 0 || $amount <= 0) {
					continue;
				}

				$elementInQueue[$elementId] = true;

				$ElementTime = BuildFunctions::getBuildingTime($USER, $PLANET, $elementId);
				$QueueTime  += $ElementTime * $amount;

				$Shipyard[] = [$LNG['tech'][$elementId] ?? ('ID '.$elementId), $amount, $ElementTime, $elementId];
			}

			$buildList = [
				'Queue'                => $Shipyard,
				'b_hangar_id_plus'      => $PLANET['b_hangar'],
				'pretty_time_b_hangar'  => pretty_time(max($QueueTime - (int)$PLANET['b_hangar'], 0)),
			];
		}

		$mode = (string)HTTP::_GP('mode', 'fleet');

		if ($mode === 'defense') {
			$elementIDs = array_merge($reslist['defense'], $reslist['missile']);
		} else {
			$elementIDs = $reslist['fleet'];
		}

		$SolarEnergy = round(((((float)$PLANET['temp_max']) + 160.0) / 6.0) * (float)$config->energySpeed, 1);

		$Missiles = [];
		foreach ($reslist['missile'] as $elementID) {
			$Missiles[$elementID] = (int)$PLANET[$resource[$elementID]];
		}

		$MaxMissiles = BuildFunctions::getMaxConstructibleRockets($USER, $PLANET, $Missiles);

		foreach ($elementIDs as $Element) {
			if (!BuildFunctions::isTechnologieAccessible($USER, $PLANET, $Element) && !$config->show_unlearned_ships) {
				continue;
			}

			$costResources = BuildFunctions::getElementPrice($USER, $PLANET, $Element);
			$costOverflow  = BuildFunctions::getRestPrice($USER, $PLANET, $Element, $costResources);

			$elementTime   = BuildFunctions::getBuildingTime($USER, $PLANET, $Element, $costResources);
			$buyable       = BuildFunctions::isElementBuyable($USER, $PLANET, $Element, $costResources);
			$maxBuildable  = BuildFunctions::getMaxConstructibleElements($USER, $PLANET, $Element, $costResources);

			if (isset($MaxMissiles[$Element])) {
				$maxBuildable = min($maxBuildable, $MaxMissiles[$Element]);
			}

			$AlreadyBuild = in_array($Element, $reslist['one'], true)
				&& (isset($elementInQueue[$Element]) || (int)$PLANET[$resource[$Element]] !== 0);

			$requireArray = [];
			if (isset($requeriments[$Element]) && is_array($requeriments[$Element])) {
				foreach ($requeriments[$Element] as $requireID => $requireLevel) {
					$requireID = (int)$requireID;
					$requireArray[] = [
						'currentLevel' => ($requireID < 100)
							? (int)$PLANET[$resource[$requireID]]
							: (int)$USER[$resource[$requireID]],
						'neededLevel'  => (int)$requireLevel,
						'requireID'    => $requireID,
					];
				}
			}

			$elementList[$Element] = [
				'id'                   => $Element,
				'available'            => $PLANET[$resource[$Element]],
				'costResources'        => $costResources,
				'costOverflow'         => $costOverflow,
				'costOverflowTotal'    => array_sum($costOverflow),
				'elementTime'          => $elementTime,
				'buyable'              => $buyable,
				'maxBuildable'         => floatToString($maxBuildable),
				'AlreadyBuild'         => $AlreadyBuild,
				'technologySatisfied'  => BuildFunctions::isTechnologieAccessible($USER, $PLANET, $Element),
				'requeriments'         => $requireArray,
			];
		}

		$this->assign([
			'elementList'         => $elementList,
			'NotBuilding'         => $NotBuilding,
			'BuildList'           => $buildList,
			'maxlength'           => strlen((string)$config->max_fleet_per_build),
			'mode'                => $mode,
			'SolarEnergy'         => $SolarEnergy,
			'userFleetPoints'     => pretty_number($USER['fleet_points']),
			'userDefensePoints'   => pretty_number($USER['defs_points']),
		]);

		$this->display('page.shipyard.default.tpl');
	}
}
