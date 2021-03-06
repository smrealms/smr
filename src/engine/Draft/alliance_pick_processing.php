<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (!is_numeric($var['PickedAccountID'])) {
	create_error('You have to pick a player.');
}

require_once(get_file_loc('alliance_pick.inc.php'));
$teams = get_draft_teams($player->getGameID());
if (!$teams[$player->getAccountID()]['CanPick']) {
	create_error('You have to wait for others to pick first.');
}
$pickedPlayer = SmrPlayer::getPlayer($var['PickedAccountID'], $player->getGameID());

if ($pickedPlayer->isDraftLeader()) {
	create_error('You cannot pick another leader.');
}

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
$db = Smr\Database::getInstance();
$db->write('INSERT INTO draft_history (game_id, leader_account_id, picked_account_id, time) VALUES(' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeNumber($pickedPlayer->getAccountID()) . ', ' . $db->escapeNumber(Smr\Epoch::time()) . ')');

Page::create('skeleton.php', 'alliance_pick.php')->go();
