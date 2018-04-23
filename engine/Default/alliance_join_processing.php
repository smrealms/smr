<?php

require_once(get_file_loc('SmrAlliance.class.inc'));

// is account validated?
if (!$account->isValidated()) {
	create_error('You are not validated. You can\'t join an alliance yet.');
}

$alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());

$canJoin = $alliance->canJoinAlliance($player);
if ($canJoin !== true) {
	create_error($canJoin);
}

if ($_REQUEST['password'] != $alliance->getPassword()) {
	create_error('Incorrect Password!');
}

// assign the player to the current alliance
$player->joinAlliance($alliance->getAllianceID());
$player->update();

forward(create_container('skeleton.php', 'alliance_roster.php'));
