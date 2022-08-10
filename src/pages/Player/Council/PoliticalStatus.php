<?php declare(strict_types=1);

use Smr\Race;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$player = $session->getPlayer();

		$raceID = $var['race_id'] ?? $player->getRaceID();

		$template->assign('PageTopic', 'Ruling Council Of ' . Race::getName($raceID));

		// echo menu
		Menu::council($raceID);

		$raceRelations = Globals::getRaceRelations($player->getGameID(), $raceID);

		$peaceRaces = [];
		$neutralRaces = [];
		$warRaces = [];
		foreach (Race::getPlayableIDs() as $otherRaceID) {
			if ($raceID != $otherRaceID) {
				if ($raceRelations[$otherRaceID] >= RELATIONS_PEACE) {
					$peaceRaces[] = $otherRaceID;
				} elseif ($raceRelations[$otherRaceID] <= RELATIONS_WAR) {
					$warRaces[] = $otherRaceID;
				} else {
					$neutralRaces[] = $otherRaceID;
				}
			}
		}

		$template->assign('PeaceRaces', $peaceRaces);
		$template->assign('NeutralRaces', $neutralRaces);
		$template->assign('WarRaces', $warRaces);
