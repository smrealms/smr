<?php declare(strict_types=1);

$alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());

$joinRestriction = $alliance->getJoinRestriction($player);
if ($joinRestriction !== false) {
	create_error($joinRestriction);
}

if (Request::get('password') != $alliance->getPassword()) {
	create_error('Incorrect Password!');
}

// assign the player to the current alliance
$player->joinAlliance($alliance->getAllianceID());
$player->update();

forward(create_container('skeleton.php', 'alliance_roster.php'));
