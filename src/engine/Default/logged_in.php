<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

// update last login time
$account->updateLastLogin();

if ($session->hasGame()) {
	$body = 'current_sector.php';
} else {
	$body = 'game_play.php';
}
Page::create($body)->go();
