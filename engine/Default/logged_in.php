<?php

// update last login time
$account->updateLastLogin();

$container = create_container('skeleton.php');
if (SmrSession::$game_id > 0) {
	$container['body'] = 'current_sector.php';
} else {
	$container['body'] = 'game_play.php';
}

forward($container);

?>
