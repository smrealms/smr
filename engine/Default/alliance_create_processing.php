<?php

if ($player->getAllianceJoinable() > TIME) {
	create_error('You cannot create an alliance for another ' . format_time($player->getAllianceJoinable() - TIME) . '.');
}

// disallow certain ascii chars
for ($i = 0; $i < strlen($_REQUEST['name']); $i++) {
	if (ord($_REQUEST['name'][$i]) < 32 || ord($_REQUEST['name'][$i]) > 127) {
		create_error('The alliance name contains invalid characters!');
	}
}

// trim input first
$name = trim($_REQUEST['name']);
$password = $_REQUEST['password'];
$description = $_REQUEST['description'];
$recruit = $_REQUEST['recruit'] == 'yes';
$perms = $_REQUEST['Perms'];
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
$alliance->update();

// assign the player to the created alliance
$player->setAllianceID($alliance->getAllianceID());
$player->update();

$alliance->createDefaultRoles($perms);

forward(create_container('skeleton.php', 'alliance_roster.php'));
