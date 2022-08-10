<?php declare(strict_types=1);

use Smr\SectorLock;

		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$account = $session->getAccount();

		// register game_id
		$session->updateGame($var['game_id']);

		$player = SmrPlayer::getPlayer($account->getAccountID(), $var['game_id']);

		// skip var update in do_voodoo
		SectorLock::getInstance()->acquireForPlayer($player);

		$player->updateLastCPLAction();

		// Check to see if newbie status has changed
		$player->updateNewbieStatus();

		// get rid of old plotted course
		$player->deletePlottedCourse();
		$player->update();

		// log
		$player->log(LOG_TYPE_GAME_ENTERING, 'Player entered game ' . $player->getGameID());

		$container = Page::create('current_sector.php');
		$container->go();
