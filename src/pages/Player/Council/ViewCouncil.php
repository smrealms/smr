<?php declare(strict_types=1);

use Smr\CouncilVoting;
use Smr\Race;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$player = $session->getPlayer();

		$raceID = $var['race_id'] ?? $player->getRaceID();

		$template->assign('PageTopic', 'Ruling Council Of ' . Race::getName($raceID));
		$template->assign('RaceID', $raceID);

		Menu::council($raceID);

		// check for relations here
		CouncilVoting::modifyRelations($raceID, $player->getGameID());
		CouncilVoting::checkPacts($raceID, $player->getGameID());
