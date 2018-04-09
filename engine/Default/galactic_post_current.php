<?php

$db->query('SELECT * FROM galactic_post_paper WHERE online_since IS NOT NULL AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY online_since DESC LIMIT 1');
if ($db->nextRecord()) {
	$paper_id = $db->getField('paper_id');
} else {
	$paper_id = null;
}

$container = create_container('skeleton.php', 'galactic_post_read.php');
$container['paper_id'] = $paper_id;
$container['game_id'] = $player->getGameID();
forward($container);
