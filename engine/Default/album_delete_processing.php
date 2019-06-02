<?php

if ($_REQUEST['action'] == 'Yes') {
	$db->query('DELETE
				FROM album
				WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . ' LIMIT 1');

	$db->query('DELETE
				FROM album_has_comments
				WHERE album_id = ' . $db->escapeNumber($account->getAccountID()));
}

$container = create_container('skeleton.php');
if (!is_object($player)) {
	$container['body'] = 'game_play.php';
} else {
	$container['body'] = 'current_sector.php';
}

forward($container);
