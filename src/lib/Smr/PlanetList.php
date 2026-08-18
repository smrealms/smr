<?php declare(strict_types=1);

namespace Smr;

use Exception;

/**
 * Collection of functions to help prepare Planet List pages.
 */
class PlanetList {

	/**
	 * The engine files for planet lists have a lot in common, so do
	 * most of the work here.
	 *
	 * @return array{Alliance: ?Alliance, PlayerPlanet: ?Planet, AllPlanets: array<Planet>}
	 */
	public static function common(int $allianceId, bool $getPlanets): array {
		$player = Session::getInstance()->getPlayer();

		$playerOnly = $allianceId === 0;
		if ($playerOnly && $player->hasAlliance()) {
			// This page doesn't support this combination
			throw new Exception('Sanity check failed!');
		}
		if (!$playerOnly) {
			$alliance = Alliance::getAlliance($allianceId, $player->getGameID());
		}

		// We might not assign the planet lists if the info is private.
		$allPlanets = [];
		if ($getPlanets) {
			// Get this player's planet if no alliance or viewing own alliance
			if ($playerOnly || $player->getAllianceID() === $allianceId) {
				$playerPlanet = $player->getPlanet();
			}

			// Get full list of planets
			if (isset($alliance)) {
				$allPlanets = $alliance->getPlanets();
			} elseif (isset($playerPlanet)) {
				$allPlanets[] = $playerPlanet;
			}
		}

		return [
			'Alliance' => $alliance ?? null,
			'PlayerPlanet' => $playerPlanet ?? null,
			'AllPlanets' => $allPlanets,
		];
	}

}
