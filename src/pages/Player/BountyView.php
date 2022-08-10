<?php declare(strict_types=1);

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$player = $session->getPlayer();

		$bountyPlayer = SmrPlayer::getPlayer($var['id'], $player->getGameID());
		$template->assign('PageTopic', 'Viewing Bounties');
		$template->assign('BountyPlayer', $bountyPlayer);
