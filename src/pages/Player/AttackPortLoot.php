<?php declare(strict_types=1);

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		$template->assign('PageTopic', 'Looting The Port');
		$template->assign('ThisPort', $player->getSectorPort());
