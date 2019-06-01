<?php

// Remove any expired invitations
$db->query('DELETE FROM alliance_invites_player WHERE expires < ' . $db->escapeNumber(TIME));

// Check that the invitation is registered in the database
$newAlliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$db->query('SELECT 1 FROM alliance_invites_player
            WHERE game_id = '.$db->escapeNumber($player->getGameID()) . '
              AND alliance_id = '.$db->escapeNumber($newAlliance->getAllianceID()) . '
              AND account_id = '.$db->escapeNumber($player->getAccountID()));
if (!$db->nextRecord()) {
	create_error('You do not have an invitation to join this alliance!');
}

// Make sure the player can join the new alliance before leaving the current one
$canJoin = $newAlliance->canJoinAlliance($player, false);
if ($canJoin !== true) {
	create_error($canJoin);
}

// Leave current alliance
if ($player->hasAlliance()) {
	if ($player->isAllianceLeader()) {
		create_error('You are the alliance leader! You must handover leadership first.');
	}
	$player->leaveAlliance();
}

// Join new alliance (this deletes the invitation)
$player->joinAlliance($newAlliance->getAllianceID());

$container = create_container('skeleton.php', 'alliance_mod.php');
forward($container);
