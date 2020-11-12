<?php declare(strict_types=1);

// Get the selected game
$game_id = $var['selected_game_id'];

// Clear any messages from prior processing
SmrSession::updateVar('processing_msg', null);

// Get the POST variables
$player_id = Request::getInt('player_id');
$action = Request::get('submit');

try {
	$selected_player = SmrPlayer::getPlayer($player_id, $game_id);
} catch (PlayerNotFoundException $e) {
	$msg = "<span class='red'>ERROR: </span>" . $e->getMessage();
	SmrSession::updateVar('processing_msg', $msg);
	forward(create_container('skeleton.php', 'manage_post_editors.php', $var));
}

$name = $selected_player->getDisplayName();
$game = $selected_player->getGame()->getDisplayName();

if ($action == "Assign") {
	if ($selected_player->isGPEditor()) {
		$msg = "<span class='red'>ERROR: </span>$name is already an editor in game $game!";
	} else {
		$db->query('INSERT INTO galactic_post_writer (player_id, game_id) VALUES (' . $db->escapeNumber($player_id) . ', ' . $db->escapeNumber($game_id) . ')');
	}
} elseif ($action == "Remove") {
	if (!$selected_player->isGPEditor()) {
		$msg = "<span class='red'>ERROR: </span>$name is not an editor in game $game!";
	} else {
		$db->query('DELETE FROM galactic_post_writer WHERE ' . $selected_player->getSQL());
	}
} else {
	$msg = "<span class='red'>ERROR: </span>Do not know action '$action'!";
}

if (!empty($msg)) {
	SmrSession::updateVar('processing_msg', $msg);
}

// Pass entire $var so that the selected game remains selected
forward(create_container('skeleton.php', 'manage_post_editors.php', $var));
