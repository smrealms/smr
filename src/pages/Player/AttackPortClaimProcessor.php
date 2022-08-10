<?php declare(strict_types=1);

		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		$port = $player->getSectorPort();
		$port->setRaceID($player->getRaceID());

		$port->getLootHREF(true)->go();
