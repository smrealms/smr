<?php declare(strict_types=1);

use Smr\Race;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		$template->assign('PageTopic', 'Trader Relations');

		Menu::trader();

		$politicalRelations = [];
		$personalRelations = [];

		$raceRelations = Globals::getRaceRelations($player->getGameID(), $player->getRaceID());
		foreach (Race::getAllNames() as $raceID => $raceName) {
			$politicalRelations[$raceName] = $raceRelations[$raceID];
			$personalRelations[$raceName] = $player->getPersonalRelation($raceID);
		}
		$template->assign('PoliticalRelations', $politicalRelations);
		$template->assign('PersonalRelations', $personalRelations);
