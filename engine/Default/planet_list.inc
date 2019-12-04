<?php declare(strict_types=1);

// The engine files for planet lists have a lot in common, so do
// most of the work here.
function planet_list_common($allianceId, $getPlanets) {
	global $template, $player;

	$playerOnly = $allianceId == 0;
	if ($playerOnly && $player->hasAlliance()) {
		// This page doesn't support this combination
		create_error('Internal error. Please report this to an admin!');
	}
	$template->assign('PlayerOnly', $playerOnly);

	if ($playerOnly) {
		$template->assign('PageTopic', 'Planet');
	} else {
		$alliance = SmrAlliance::getAlliance($allianceId, $player->getGameID());
		$template->assign('Alliance', $alliance);
		$template->assign('PageTopic', 'Planets : ' . $alliance->getAllianceName());
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
		$allPlanets = array();
		if (!$playerOnly) {
			$allPlanets = $alliance->getPlanets();
		} elseif ($playerPlanet !== false) {
			$allPlanets[] = $playerPlanet;
		}
		$template->assign('AllPlanets', $allPlanets);
	}
}
