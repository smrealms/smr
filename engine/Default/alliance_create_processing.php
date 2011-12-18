<?php

if($player->getAllianceJoinable() > TIME) {
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
if($name != $filteredName) {
	create_error('The alliance name contains one or more filtered words, please reconsider the name.');
}

// check if the alliance name already exist
$db->query('SELECT 1 FROM alliance WHERE alliance_name = ' . $db->escapeString($name) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' LIMIT 1');
if ($db->getNumRows() > 0) {
	create_error('That alliance name already exists!');
}

// get the next alliance id
$db->query('SELECT max(alliance_id) FROM alliance WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND (alliance_id < 302 OR alliance_id > 309) LIMIT 1');
$db->nextRecord();
$alliance_id = $db->getInt('max(alliance_id)') + 1;
if($alliance_id >= 302 && $alliance_id <= 309) {
	$alliance_id = 310;
}


$description = word_filter($description);
$player->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS,'Alliance '.$name.'('.$alliance_id.') had their description changed to:'.EOL.EOL.$description);
// actually create the alliance here
$db->query('INSERT INTO alliance (alliance_id, game_id, alliance_name, alliance_description, alliance_password, leader_id, recruiting) ' .
						  'VALUES(' . $db->escapeNumber($alliance_id) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeString($name) . ', ' . $db->escapeString($description) . ', ' . $db->escapeString($password) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeBoolean($recruit) . ')');

// assign the player to the current alliance
$player->setAllianceID($alliance_id);
$player->update();

$withPerDay = ALLIANCE_BANK_UNLIMITED;
$removeMember = TRUE;
$changePass = TRUE;
$changeMOD = TRUE;
$changeRoles = TRUE;
$planetAccess = TRUE;
$exemptWith = TRUE;
$mbMessages = TRUE;
$sendAllMsg = TRUE;
$db->query('INSERT INTO alliance_has_roles (alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg) ' .
			'VALUES (' . $db->escapeNumber($alliance_id) . ', ' . $db->escapeNumber($player->getGameID()) . ', 1, \'Leader\', ' . $db->escapeNumber($withPerDay) . ', '.$db->escapeBoolean($removeMember) . ', ' . $db->escapeBoolean($changePass) . ', ' . $db->escapeBoolean($changeMOD) . ', '.$db->escapeBoolean($changeRoles) . ', ' . $db->escapeBoolean($planetAccess) . ', ' . $db->escapeBoolean($exemptWith) . ', ' . $db->escapeBoolean($mbMessages) . ', ' . $db->escapeString($sendAllMsg) . ')');
switch ($perms) {
	case 'full':
		//do nothing, perms already set above.
	break;
	case 'none':
		$withPerDay = 0;
		$removeMember = FALSE;
		$changePass = FALSE;
		$changeMOD = FALSE;
		$changeRoles = FALSE;
		$planetAccess = FALSE;
		$exemptWith = FALSE;
		$mbMessages = FALSE;
		$sendAllMsg = FALSE;
	break;
	case 'basic':
		$withPerDay = ALLIANCE_BANK_UNLIMITED;
		$removeMember = FALSE;
		$changePass = FALSE;
		$changeMOD = FALSE;
		$changeRoles = FALSE;
		$planetAccess = TRUE;
		$exemptWith = FALSE;
		$mbMessages = FALSE;
		$sendAllMsg = FALSE;
	break;
}
$db->query('INSERT INTO alliance_has_roles (alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg) ' .
			'VALUES (' . $db->escapeNumber($alliance_id) . ', ' . $db->escapeNumber($player->getGameID()) . ', 2, \'New Member\', ' . $db->escapeNumber($withPerDay) . ', '.$db->escapeBoolean($removeMember) . ', ' . $db->escapeBoolean($changePass) . ', ' . $db->escapeBoolean($changeMOD) . ', '.$db->escapeBoolean($changeRoles) . ', ' . $db->escapeBoolean($planetAccess) . ', ' . $db->escapeBoolean($exemptWith) . ', ' . $db->escapeBoolean($mbMessages) . ', ' . $db->escapeString($sendAllMsg) . ')');
$db->query('INSERT INTO player_has_alliance_role (game_id, account_id, role_id,alliance_id) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getAccountID()) . ', 1,' . $db->escapeNumber($alliance_id) . ')');
forward(create_container('skeleton.php', 'alliance_roster.php'));

?>