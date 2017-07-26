<?php

if (!empty($_POST['game_id'])) {
	// Enable the requested game
	$game_id = $db->escapeNumber($_POST['game_id']);
	$enabled = $db->escapeBoolean(true);
	$db->query("UPDATE game SET enabled=$enabled WHERE game_id=$game_id");

	$game = SmrGame::getGame($_POST['game_id'])->getDisplayName();
	$msg = "<span class='green'>SUCCESS: </span>Enabled game $game.";
}

forward(create_container('skeleton.php', 'enable_game.php',
                         array('processing_msg' => $msg)));

?>
