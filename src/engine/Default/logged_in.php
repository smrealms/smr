<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

// update last login time
$account->updateLastLogin();

$container = Page::create('skeleton.php');
if ($session->hasGame()) {
	$container['body'] = 'current_sector.php';
} else {
	$container['body'] = 'game_play.php';
}

$container->go();
