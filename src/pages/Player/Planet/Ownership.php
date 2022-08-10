<?php declare(strict_types=1);

		require_once(LIB . 'Default/planet.inc.php');
		planet_common();

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		$container = Page::create('planet_ownership_processing.php');
		$template->assign('ProcessingHREF', $container->href());

		$template->assign('Planet', $player->getSectorPlanet());
		$template->assign('Player', $player);

		// Check if this player already owns a planet
		$playerPlanet = $player->getPlanet();
		if ($playerPlanet !== null) {
			$template->assign('PlayerPlanet', $playerPlanet->getSectorID());
		}
