<?php declare(strict_types=1);

$session = Smr\Session::getInstance();

// Reset the game ID if necessary
if ($session->hasGame()) {
	$account->log(LOG_TYPE_GAME_ENTERING, 'Player left game ' . $session->getGameID());
	$session->updateGame(0);
}

$session->clearLinks();

Page::create('skeleton.php', $var['body'], $var)->go();
