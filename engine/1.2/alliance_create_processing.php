<?php

// disallow certain ascii chars
for ($i = 0; $i < strlen($_POST["name"]); $i++)
	if (ord($_POST["name"][$i]) < 32 || ord($_POST["name"][$i]) > 127)
		create_error("The alliance name contains invalid characters!");

// trim input first
$name = trim($_POST["name"]);
$password = $_REQUEST['password'];
$description = $_REQUEST['description'];
$recruit = $_REQUEST['recruit'];
if ($recruit == "yes") $recruit = TRUE;
else $recruit = FALSE;
$perms = $_REQUEST['Perms'];
if (empty($password))
	create_error("You must enter a password!");
if (empty($description))
	create_error("You must enter a description!");
if (empty($name))
	create_error("You must enter an alliance name!");
$name2 = strtolower($name);
if ($name2 == "none" || $name == "(none)" || $name == "( none )" || $name == "no alliance")
	create_error("That is not a valid alliance name!");

// check if the alliance name already exist
$db->query("SELECT * FROM alliance WHERE alliance_name = " . format_string($name, true) . " AND " .
										"game_id = ".SmrSession::$game_id);
if ($db->nf() > 0)
	create_error("That alliance name already exists!");

// get the next alliance id
$db->query("SELECT * FROM alliance WHERE game_id = ".SmrSession::$game_id." AND (alliance_id < 302 OR alliance_id > 309) ORDER BY alliance_id DESC LIMIT 1");
$db->next_record();
$alliance_id = $db->f("alliance_id") + 1;

// actually create the alliance here
$db->query("INSERT INTO alliance (alliance_id, game_id, alliance_name, alliance_description, alliance_password, leader_id, recruiting) " .
						  "VALUES($alliance_id, ".SmrSession::$game_id.", " . format_string($name, true) . ", " . format_string($description, false) . ", '$password', ".SmrSession::$old_account_id.", '$recruit')");

// assign the player to the current alliance
$db->query("UPDATE player SET alliance_id = $alliance_id WHERE account_id = ".SmrSession::$old_account_id." AND " .
															  "game_id = ".SmrSession::$game_id);

$withPerDay = -2;
$removeMember = TRUE;
$changePass = TRUE;
$changeMOD = TRUE;
$changeRoles = TRUE;
$planetAccess = TRUE;
$exemptWith = TRUE;
$mbMessages = TRUE;
$sendAllMsg = TRUE;
$db->query("INSERT INTO alliance_has_roles (alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg) " .
			"VALUES ($alliance_id, ".SmrSession::$game_id.", 1, 'Leader', $withPerDay, '$removeMember', '$changePass', '$changeMOD', '$changeRoles', '$planetAccess', '$exemptWith', '$mbMessages', '$sendAllMsg')");
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
		$withPerDay = -2;
		$removeMember = FALSE;
		$changePass = FALSE;
		$changeMOD = FALSE;
		$changeRoles = FALSE;
		$planetAccess = TRUE;
		$exemptWith = FALSE;
		$mbMessages = FALSE;
		$sendAllMsg = FALSE;
		break;
	default:
		break;
}
$db->query("INSERT INTO alliance_has_roles (alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg) " .
			"VALUES ($alliance_id, ".SmrSession::$game_id.", 2, 'New Member', $withPerDay, '$removeMember', '$changePass', '$changeMOD', '$changeRoles', '$planetAccess', '$exemptWith', '$mbMessages', '$sendAllMsg')");
$db->query("INSERT INTO player_has_alliance_role (game_id, account_id, role_id,alliance_id) VALUES ($player->game_id, $player->account_id, 1,$alliance_id)");
forward(create_container("skeleton.php", "alliance_roster.php"));

?>