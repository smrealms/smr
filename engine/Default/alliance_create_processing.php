<?php declare(strict_types=1);

if ($player->getAllianceJoinable() > TIME) {
	create_error('You cannot create an alliance for another ' . format_time($player->getAllianceJoinable() - TIME) . '.');
}

// trim input first
$name = trim(Request::get('name'));

// disallow certain ascii chars
for ($i = 0; $i < strlen($name); $i++) {
	if (ord($name[$i]) < 32 || ord($name[$i]) > 127) {
		create_error('The alliance name contains invalid characters!');
	}
}

$password = Request::get('password');
$description = Request::get('description');
$recruit = Request::get('recruit') == 'yes';
$perms = Request::get('Perms');
if (empty($password)) {
	create_error('You must enter a password!');
}
if (empty($name)) {
	create_error('You must enter an alliance name!');
}
$name2 = strtolower($name);
if ($name2 == 'none' || $name2 == '(none)' || $name2 == '( none )' || $name2 == 'no alliance') {
	create_error('That is not a valid alliance name!');
}
$filteredName = word_filter($name);
if ($name != $filteredName) {
	create_error('The alliance name contains one or more filtered words, please reconsider the name.');
}

// create the alliance
$alliance = SmrAlliance::createAlliance($player->getGameID(), $name, $password, $recruit);
$alliance->setAllianceDescription($description);
$alliance->setLeaderID($player->getAccountID());
$alliance->createDefaultRoles($perms);
$alliance->update();

// assign the player to the created alliance
$player->joinAlliance($alliance->getAllianceID());
$player->update();

forward(create_container('skeleton.php', 'alliance_roster.php'));
