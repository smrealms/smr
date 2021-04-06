<?php declare(strict_types=1);

// update last login time
$account->updateLastLogin();

$container = Page::create('skeleton.php');
if (Smr\Session::getInstance()->hasGame()) {
	$container['body'] = 'current_sector.php';
} else {
	$container['body'] = 'game_play.php';
}

$container->go();
