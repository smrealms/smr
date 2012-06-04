<?php

require_once(get_file_loc('smr_alliance.inc'));

// ********************************
// *
// * V a l i d a t e d ?
// *
// ********************************

// is account validated?
if (!$account->isValidated())
{
	create_error('You are not validated. You can\'t join an alliance yet.');
}
	

// ********************************
// *
// * B e g i n
// *
// ********************************

$alliance = new SMR_ALLIANCE($var['alliance_id'], SmrSession::$game_id);

if ($alliance->canJoinAlliance($player) !== true)
{
	create_error('You are not able to join this alliance currently.');
}

$password = $_REQUEST['password'];

if ($password != $alliance->getPassword())
	create_error('Incorrect Password!');

// assign the player to the current alliance
$player->joinAlliance($alliance->getAllianceID());
$player->update();

forward(create_container('skeleton.php', 'alliance_roster.php'));

?>