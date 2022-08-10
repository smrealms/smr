<?php declare(strict_types=1);

		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$player = $session->getPlayer();

		if (!$player->isLandedOnPlanet()) {
			create_error('You are not on a planet!');
		}
		$planet = $player->getSectorPlanet();
		$action = $var['action'];

		/** @var int $constructionID */
		$constructionID = $var['construction_id'];

		if ($action == 'Build') {
			// now start the construction
			$planet->startBuilding($player, $constructionID);
			$player->increaseHOF(1, ['Planet', 'Buildings', 'Started'], HOF_ALLIANCE);

			$player->log(LOG_TYPE_PLANETS, 'Player starts a ' . $planet->getStructureTypes($constructionID)->name() . ' on planet.');

		} elseif ($action == 'Cancel') {
			$planet->stopBuilding($constructionID);
			$player->increaseHOF(1, ['Planet', 'Buildings', 'Stopped'], HOF_ALLIANCE);
			$player->log(LOG_TYPE_PLANETS, 'Player cancels planet construction');
		}

		Page::create('planet_construction.php')->go();
