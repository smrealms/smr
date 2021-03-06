<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

// Get the selected game
$game_id = $var['selected_game_id'];

// Clear any messages from prior processing
$session->updateVar('processing_msg', null);

// Get the POST variables
$player_id = Request::getInt('player_id');
$action = Request::get('submit');

try {
	$selected_player = SmrPlayer::getPlayerByPlayerID($player_id, $game_id);
} catch (PlayerNotFoundException $e) {
	$msg = "<span class='red'>ERROR: </span>" . $e->getMessage();
	$session->updateVar('processing_msg', $msg);
	Page::create('skeleton.php', 'manage_post_editors.php', $var)->go();
}

$name = $selected_player->getDisplayName();
$account_id = $selected_player->getAccountID();
$game = $selected_player->getGame()->getDisplayName();

if ($action == "Assign") {
	if ($selected_player->isGPEditor()) {
		$msg = "<span class='red'>ERROR: </span>$name is already an editor in game $game!";
	} else {
		$db->write('INSERT INTO galactic_post_writer (account_id, game_id) VALUES (' . $db->escapeNumber($account_id) . ', ' . $db->escapeNumber($game_id) . ')');
	}
} elseif ($action == "Remove") {
	if (!$selected_player->isGPEditor()) {
		$msg = "<span class='red'>ERROR: </span>$name is not an editor in game $game!";
	} else {
		$db->write('DELETE FROM galactic_post_writer WHERE ' . $selected_player->getSQL());
	}
} else {
	$msg = "<span class='red'>ERROR: </span>Do not know action '$action'!";
}

if (!empty($msg)) {
	$session->updateVar('processing_msg', $msg);
}

// Pass entire $var so that the selected game remains selected
Page::create('skeleton.php', 'manage_post_editors.php', $var)->go();
