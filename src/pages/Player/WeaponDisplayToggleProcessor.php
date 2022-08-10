<?php declare(strict_types=1);

		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		$player->setDisplayWeapons(!$player->isDisplayWeapons());
		// If this is called by ajax, we don't want to do any forwarding
		if ($session->ajax) {
			exit;
		}

		Page::create('current_sector.php')->go();
