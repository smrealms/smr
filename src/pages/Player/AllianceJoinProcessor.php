<?php declare(strict_types=1);

use Smr\Request;

		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$player = $session->getPlayer();

		$alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());

		$joinRestriction = $alliance->getJoinRestriction($player);
		if ($joinRestriction !== false) {
			create_error($joinRestriction);
		}

		// Open recruitment implies an empty password
		if (Request::get('password', '') != $alliance->getPassword()) {
			create_error('Incorrect Password!');
		}

		// assign the player to the current alliance
		$player->joinAlliance($alliance->getAllianceID());
		$player->update();

		Page::create('alliance_roster.php')->go();
