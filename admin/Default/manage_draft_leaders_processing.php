<?php declare(strict_types=1);

// Get the selected game
$gameId = $var['selected_game_id'];

// Clear any messages from prior processing
SmrSession::updateVar('processing_msg', null);

// Get the POST variables
$playerID = Request::getInt('player_id');
$homeSectorID = Request::getInt('home_sector_id');
$action = Request::get('submit');

try {
	$selectedPlayer = SmrPlayer::getPlayer($playerID, $gameId);
} catch (PlayerNotFoundException $e) {
	$msg = "<span class='red'>ERROR: </span>" . $e->getMessage();
	SmrSession::updateVar('processing_msg', $msg);
	forward(create_container('skeleton.php', 'manage_draft_leaders.php', $var));
}

$name = $selectedPlayer->getDisplayName();
$game = $selectedPlayer->getGame()->getDisplayName();

if ($action == "Assign") {
	if ($selectedPlayer->isDraftLeader()) {
		$msg = "<span class='red'>ERROR: </span>$name is already a draft leader in game $game!";
	} else {
		$db->query('INSERT INTO draft_leaders (player_id, game_id, home_sector_id) VALUES (' . $db->escapeNumber($playerID) . ', ' . $db->escapeNumber($gameId) . ', ' . $db->escapeNumber($homeSectorID) . ')');
	}
} elseif ($action == "Remove") {
	if (!$selectedPlayer->isDraftLeader()) {
		$msg = "<span class='red'>ERROR: </span>$name is not a draft leader in game $game!";
	} else {
		$db->query('DELETE FROM draft_leaders WHERE ' . $selectedPlayer->getSQL());
	}
} else {
	$msg = "<span class='red'>ERROR: </span>Do not know action '$action'!";
}

if (!empty($msg)) {
	SmrSession::updateVar('processing_msg', $msg);
}

// Pass entire $var so that the selected game remains selected
forward(create_container('skeleton.php', 'manage_draft_leaders.php', $var));
