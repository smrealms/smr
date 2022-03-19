<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if ($player->getAllianceJoinable() > Smr\Epoch::time()) {
	create_error('You cannot create an alliance for another ' . format_time($player->getAllianceJoinable() - Smr\Epoch::time()) . '.');
}

// trim input first
$name = trim(Smr\Request::get('name'));
if (empty($name)) {
	throw new Exception('No alliance name entered');
}

// disallow certain ascii chars
if (!ctype_print($name)) {
	create_error('The alliance name contains invalid characters!');
}

$password = trim(Smr\Request::get('password', ''));
$description = Smr\Request::get('description');
$recruitType = Smr\Request::get('recruit_type');
$perms = Smr\Request::get('Perms');

$name2 = strtolower($name);
if ($name2 == 'none' || $name2 == '(none)' || $name2 == '( none )' || $name2 == 'no alliance') {
	create_error('That is not a valid alliance name!');
}
$filteredName = word_filter($name);
if ($name != $filteredName) {
	create_error('The alliance name contains one or more filtered words, please reconsider the name.');
}

// create the alliance
$alliance = SmrAlliance::createAlliance($player->getGameID(), $name);
$alliance->setRecruitType($recruitType, $password);
$alliance->setAllianceDescription($description, $player);
$alliance->setLeaderID($player->getAccountID());
$alliance->createDefaultRoles($perms);
$alliance->update();

// assign the player to the created alliance
$player->joinAlliance($alliance->getAllianceID());
$player->update();

Page::create('skeleton.php', 'alliance_roster.php')->go();
