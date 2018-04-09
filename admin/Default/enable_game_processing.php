<?php

if (!empty($_POST['game_id'])) {
	// Enable the requested game
	$db->query('UPDATE game SET enabled=' . $db->escapeBoolean(true) .
	           ' WHERE game_id=' . $db->escapeNumber($_POST['game_id']));

	$game = SmrGame::getGame($_POST['game_id'])->getDisplayName();
	$msg = "<span class='green'>SUCCESS: </span>Enabled game $game.";
}

forward(create_container('skeleton.php', 'enable_game.php',
                         array('processing_msg' => $msg)));
