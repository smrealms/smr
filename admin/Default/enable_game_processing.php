<?php

// Enable the requested game
$game_id_post = $_POST['game_id'];
if (!empty($game_id_post)) {
	$enabled = $db->escapeBoolean(true);
	$game_id = $db->escapeNumber($game_id_post);
	$db->query("UPDATE game SET enabled=$enabled WHERE game_id=$game_id");
	$msg = "<span class='green'>SUCCESS: </span>Enabled game $game_id.";
}

forward(create_container('skeleton.php', 'enable_game.php',
                         array('processing_msg' => $msg)));

?>
