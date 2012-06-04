<?php
$alliance_id = isset($var['alliance_id']) ? $var['alliance_id'] : $player->getAllianceID();

if ($_REQUEST['unlimited']) $withPerDay = ALLIANCE_BANK_UNLIMITED;
else $withPerDay = $_REQUEST['maxWith'];
if (!is_numeric($withPerDay) || ($withPerDay < 0 && $withPerDay != ALLIANCE_BANK_UNLIMITED)) {
	create_error('You must enter a number for max withdrawals per 24 hours.');
}
$positiveBalance = (bool)$_REQUEST['positive'];
if($withPerDay == ALLIANCE_BANK_UNLIMITED && $positiveBalance) {
	create_error('You cannot have both unlimited withdrawals and a positive balance limit.');
}
$changePass = (bool)$_REQUEST['changePW'];
$removeMember = (bool)$_REQUEST['removeMember'];
$changeMOD = (bool)$_REQUEST['changeMoD'];
$changeRoles = (bool)((isset($var['role_id']) && $var['role_id'] == 1) || $_REQUEST['changeRoles']); //Leader can always change roles.
$planetAccess = (bool)$_REQUEST['planets'];
$mbMessages = (bool)$_REQUEST['mbMessages'];
$exemptWith = (bool)$_REQUEST['exemptWithdrawals'];
$sendAllMsg = (bool)$_REQUEST['sendAllMsg'];

// with empty role the user wants to create a new entry
if (!isset($var['role_id'])) {
	// role empty too? that doesn't make sence
	if (empty($_POST['role'])) {
		create_error('You must enter a role if you want to create a new one.');
	}

	$db->lockTable('alliance_has_roles');

	// get last id
	$db->query('SELECT MAX(role_id)
				FROM alliance_has_roles
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($alliance_id));
	if ($db->nextRecord()) {
		$role_id = $db->getInt('MAX(role_id)') + 1;
	}

	$db->query('INSERT INTO alliance_has_roles
				(alliance_id, game_id, role_id, role, with_per_day, positive_balance, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg)
				VALUES (' . $db->escapeNumber($alliance_id) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($role_id) . ', ' . $db->escapeString($_POST['role']) . ', ' . $db->escapeNumber($withPerDay) . ',' . $db->escapeBoolean($positiveBalance) . ', ' . $db->escapeBoolean($removeMember) . ', ' . $db->escapeBoolean($changePass) . ', ' . $db->escapeBoolean($changeMOD) . ', ' . $db->escapeBoolean($changeRoles) . ', ' . $db->escapeBoolean($planetAccess) . ', ' . $db->escapeBoolean($exemptWith) . ', ' . $db->escapeBoolean($mbMessages) . ', ' . $db->escapeBoolean($sendAllMsg) . ')');

	$db->unlock();
}
else {
	// if no role is given we delete that entry
	if (empty($_POST['role'])) {
		if($var['role_id']==1) {
			create_error('You cannot delete the leader role.');
		}
		else if($var['role_id']==2) {
			create_error('You cannot delete the new member role.');
		}
		$db->query('DELETE FROM alliance_has_roles
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
					AND role_id = ' . $db->escapeNumber($var['role_id']));
	// otherwise we update it
	}
	else {
		$db->query('UPDATE alliance_has_roles
					SET role = ' . $db->escapeString($_POST['role']) . ',
					with_per_day = ' . $db->escapeNumber($withPerDay) . ',
					positive_balance = ' . $db->escapeBoolean($positiveBalance) . ',
					remove_member = ' . $db->escapeBoolean($removeMember) . ',
					change_pass = ' . $db->escapeBoolean($changePass) . ',
					change_mod = ' . $db->escapeBoolean($changeMOD) . ',
					change_roles = ' . $db->escapeBoolean($changeRoles) . ',
					planet_access = ' . $db->escapeBoolean($planetAccess) . ',
					exempt_with = ' . $db->escapeBoolean($exemptWith) . ',
					mb_messages = ' . $db->escapeBoolean($mbMessages) . ',
					send_alliance_msg = ' . $db->escapeBoolean($sendAllMsg) . '
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
						AND role_id = ' . $db->escapeNumber($var['role_id']));

	}

}
$container = create_container('skeleton.php', 'alliance_roles.php');
$container['alliance_id'] = $alliance_id;
forward($container);

?>