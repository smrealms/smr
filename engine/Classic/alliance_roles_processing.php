<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
// with empty role the user wants to create a new entry
if (!isset($var["role_id"])) {

	// role empty too? that doesn't make sence
	if (empty($_POST["role"]))
		create_error("You must enter a role if you wnat create a new one.");

	$db->lock("alliance_has_roles");

	// get last id
	$db->query("SELECT MAX(role_id)
				FROM alliance_has_roles
				WHERE game_id = $player->game_id AND
					  alliance_id = $alliance_id");
	if ($db->next_record())
		$role_id = $db->f("MAX(role_id)") + 1;
	else
		$role_id = 1;
	if ($_REQUEST['unlimited']) $withPerDay = -2;
	elseif ($_REQUEST['positive']) $withPerDay = -1;
	else $withPerDay = $_REQUEST['maxWith'];
	if (!is_numeric($withPerDay) || $withPerDay < -2) create_error("You must enter a number for max withdrawls per 24 hours.");
	if ($_REQUEST['changePW']) $changePass = TRUE;
	else $changePass = FALSE;
	if ($_REQUEST['removeMember']) $removeMember = TRUE;
	else $removeMember = FALSE;
	if ($_REQUEST['changeMoD']) $changeMOD = TRUE;
	else $changeMOD = FALSE;
	if ($_REQUEST['changeRoles']) $changeRoles = TRUE;
	else $changeRoles = FALSE;
	if ($_REQUEST['planets']) $planetAccess = TRUE;
	else $planetAccess = FALSE;
	if ($_REQUEST['mbMessages']) $mbMessages = TRUE;
	else $mbMessages = FALSE;
	if ($_REQUEST['exemptWithdrawls']) $exemptWith = TRUE;
	else $exemptWith = FALSE;
	if ($_REQUEST['sendAllMsg']) $sendAllMsg = TRUE;
	else $sendAllMsg = FALSE;

	$db->query("INSERT INTO alliance_has_roles
				(alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg)
				VALUES ($alliance_id, $player->game_id, $role_id, " . format_string($_POST["role"], true) . ", $withPerDay, '$removeMember', '$changePass', '$changeMOD', '$changeRoles', '$planetAccess', '$exemptWith', '$mbMessages', '$sendAllMsg')");

	$db->unlock();

} else {

	// if no role is given we delete that entry
	if (empty($_POST["role"])) {

			$db->query("DELETE FROM alliance_has_roles
						WHERE game_id = $player->game_id AND
							  alliance_id = $alliance_id AND
							  role_id = " . $var["role_id"]);

	// otherwise we update it
	} else {

		if ($_REQUEST['unlimited']) $withPerDay = -2;
		elseif ($_REQUEST['positive']) $withPerDay = -1;
		else $withPerDay = $_REQUEST['maxWith'];
		if (!is_numeric($withPerDay)) create_error("You must enter a number for max withdrawls per 24 hours.");
		if ($_REQUEST['changePW']) $changePass = TRUE;
		else $changePass = FALSE;
		if ($_REQUEST['removeMember']) $removeMember = TRUE;
		else $removeMember = FALSE;
		if ($_REQUEST['changeMoD']) $changeMOD = TRUE;
		else $changeMOD = FALSE;
		if ($_REQUEST['changeRoles']) $changeRoles = TRUE;
		else $changeRoles = FALSE;
		if ($_REQUEST['planets']) $planetAccess = TRUE;
		else $planetAccess = FALSE;
		if ($_REQUEST['mbMessages']) $mbMessages = TRUE;
		else $mbMessages = FALSE;
		if ($_REQUEST['exemptWithdrawls']) $exemptWith = TRUE;
		else $exemptWith = FALSE;
		if ($_REQUEST['sendAllMsg']) $sendAllMsg = TRUE;
		else $sendAllMsg = FALSE;
		if ($var["role_id"] == 1) $changeRoles = TRUE;
		$db->query("UPDATE alliance_has_roles
					SET role = " . format_string($_POST["role"], true) . ",
					with_per_day = $withPerDay,
					remove_member = '$removeMember',
					change_pass = '$changePass',
					change_mod = '$changeMOD',
					change_roles = '$changeRoles',
					planet_access = '$planetAccess',
					exempt_with = '$exemptWith',
					mb_messages = '$mbMessages',
					send_alliance_msg = '$sendAllMsg' 
					WHERE game_id = $player->game_id AND
						  alliance_id = $alliance_id AND
						  role_id = " . $var["role_id"]);

	}

}
$container = create_container("skeleton.php", "alliance_roles.php");
$container['alliance_id'] = $alliance_id;
forward($container);

?>