<?php declare(strict_types=1);

namespace Smr;

use Exception;
use SmrAlliance;

/**
 * Collection of functions to help prepare Planet List pages.
 */
class PlanetList {

	/**
	 * The engine files for planet lists have a lot in common, so do
	 * most of the work here.
	 */
	public static function common(int $allianceId, bool $getPlanets): void {
		$template = Template::getInstance();
		$player = Session::getInstance()->getPlayer();

		$playerOnly = $allianceId == 0;
		if ($playerOnly && $player->hasAlliance()) {
			// This page doesn't support this combination
			throw new Exception('Sanity check failed!');
		}
		$template->assign('PlayerOnly', $playerOnly);

		if ($playerOnly) {
			$template->assign('PageTopic', 'Planet');
		} else {
			$alliance = SmrAlliance::getAlliance($allianceId, $player->getGameID());
			$template->assign('Alliance', $alliance);
			$template->assign('PageTopic', 'Planets : ' . $alliance->getAllianceDisplayName());
		}

		// We might not assign the planet lists if the info is private.
		if ($getPlanets) {
			// Get this player's planet if no alliance or viewing own alliance
			if ($playerOnly || $player->getAllianceID() == $allianceId) {
				$playerPlanet = $player->getPlanet();
				if ($playerPlanet !== false) {
					$template->assign('PlayerPlanet', $playerPlanet);
				}
			}

			// Get full list of planets
			$allPlanets = [];
			if (isset($alliance)) {
				$allPlanets = $alliance->getPlanets();
			} elseif (isset($playerPlanet)) {
				$allPlanets[] = $playerPlanet;
			}
			$template->assign('AllPlanets', $allPlanets);
		}
	}

}
