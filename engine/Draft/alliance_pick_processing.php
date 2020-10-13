<?php declare(strict_types=1);
if (!is_numeric($var['PickedPlayerID'])) {
	create_error('You have to pick a player.');
}
$db->query('SELECT 1
			FROM draft_leaders
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND player_id = ' . $db->escapeNumber($var['PickedPlayerID']));
if ($db->nextRecord()) {
	create_error('You cannot pick another leader.');
}

require_once('alliance_pick.inc');
$teams = get_draft_teams($player->getGameID());
if (!$teams[$player->getPlayerID()]['CanPick']) {
	create_error('You have to wait for others to pick first.');
}
$pickedPlayer = SmrPlayer::getPlayer($var['PickedPlayerID'], $player->getGameID());

if ($pickedPlayer->hasAlliance()) {
	if ($pickedPlayer->getAllianceID() == NHA_ID) {
		$pickedPlayer->leaveAlliance();
	} else {
		create_error('Picked player already has an alliance.');
	}
}

// assign the player to the current alliance
$pickedPlayer->joinAlliance($player->getAllianceID());

// move the player to the alliance home sector if not using traditional HQ's
if ($pickedPlayer->getSectorID() === 1) {
	$pickedPlayer->setSectorID($pickedPlayer->getHome());
	$pickedPlayer->getSector()->markVisited($pickedPlayer);
}

$pickedPlayer->update();

// Update the draft history
$db->query('INSERT INTO draft_history (game_id, leader_player_id, picked_player_id, time) VALUES(' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getPlayerID()) . ', ' . $db->escapeNumber($pickedPlayer->getPlayerID()) . ', ' . $db->escapeNumber(TIME) . ')');

forward(create_container('skeleton.php', 'alliance_pick.php'));
