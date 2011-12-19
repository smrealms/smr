<?php

if ($_POST['action'] == 'Yes') {
	$db->query('DELETE
				FROM album
				WHERE account_id = ' . $db->escapeNumber(SmrSession::$account_id) . ' LIMIT 1');

	$db->query('DELETE
				FROM album_has_comments
				WHERE album_id = ' . $db->escapeNumber(SmrSession::$account_id));
}

$container = array();
$container['url'] = 'skeleton.php';
if(!is_object($player)) {
	$container['body'] = 'game_play.php';
}
else if ($player->isLandedOnPlanet()) {
	$container['body'] = 'planet_main.php';
}
else {
	$container['body'] = 'current_sector.php';
}

forward($container);

?>