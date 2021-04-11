<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

// Check that the invitation is registered in the database
try {
	$invite = SmrInvitation::get($var['alliance_id'], $player->getGameID(), $player->getAccountID());
} catch (InvitationNotFoundException $e) {
	create_error('Your invitation to join this alliance has expired or been canceled!');
}

// Make sure the player can join the new alliance before leaving the current one
$newAlliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$joinRestriction = $newAlliance->getJoinRestriction($player, false);
if ($joinRestriction !== false) {
	create_error($joinRestriction);
}

// Leave current alliance
if ($player->hasAlliance()) {
	if ($player->isAllianceLeader()) {
		create_error('You are the alliance leader! You must handover leadership first.');
	}
	$player->leaveAlliance();
}

// Join new alliance
$player->joinAlliance($newAlliance->getAllianceID());

// Delete the invitation now that the player has joined
$invite->delete();

$container = Page::create('skeleton.php', 'alliance_mod.php');
$container->go();
