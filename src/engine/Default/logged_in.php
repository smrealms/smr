<?php declare(strict_types=1);

// update last login time
$account->updateLastLogin();

$container = create_container('skeleton.php');
if (SmrSession::hasGame()) {
	$container['body'] = 'current_sector.php';
} else {
	$container['body'] = 'game_play.php';
}

forward($container);
