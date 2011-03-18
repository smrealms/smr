<?php
$alliance_id = isset($var['alliance_id']) ? $var['alliance_id'] : $player->getAllianceID();

// with empty role the user wants to create a new entry
if (!isset($var['role_id']))
{
	// role empty too? that doesn't make sence
	if (empty($_POST['role']))
	{
		create_error('You must enter a role if you want to create a new one.');
	}

	$db->lockTable('alliance_has_roles');

	// get last id
	$db->query('SELECT MAX(role_id)
				FROM alliance_has_roles
				WHERE game_id = '.$player->getGameID().' AND
					  alliance_id = '.$alliance_id);
	if ($db->nextRecord())
	{
		$role_id = $db->getField('MAX(role_id)') + 1;
	}
	else
	{
		$role_id = 1;
	}
	if ($_REQUEST['unlimited']) $withPerDay = -2;
	elseif ($_REQUEST['positive']) $withPerDay = -1;
	else $withPerDay = $_REQUEST['maxWith'];
	if (!is_numeric($withPerDay) || $withPerDay < -2) create_error('You must enter a number for max withdrawals per 24 hours.');
	$changePass = (bool)$_REQUEST['changePW'];
	$removeMember = (bool)$_REQUEST['removeMember'];
	$changeMOD = (bool)$_REQUEST['changeMoD'];
	$changeRoles = (bool)$_REQUEST['changeRoles'];
	$planetAccess = (bool)$_REQUEST['planets'];
	$mbMessages = (bool)$_REQUEST['mbMessages'];
	$exemptWith = (bool)$_REQUEST['exemptWithdrawls'];
	$sendAllMsg = (bool)$_REQUEST['sendAllMsg'];

	$db->query('INSERT INTO alliance_has_roles
				(alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg)
				VALUES ('.$alliance_id.', '.$player->getGameID().', '.$role_id.', ' . $db->escape_string($_POST['role'], true) . ', '.$withPerDay.', '.$db->escapeString($removeMember).', '.$db->escapeString($changePass).', '.$db->escapeString($changeMOD).', '.$db->escapeString($changeRoles).', '.$db->escapeString($planetAccess).', '.$db->escapeString($exemptWith).', '.$db->escapeString($mbMessages).', '.$db->escapeString($sendAllMsg).')');

	$db->unlock();
}
else
{
	// if no role is given we delete that entry
	if (empty($_POST['role']))
	{
		if($var['role_id']==1)
		{
			create_error('You cannot delete the leader role.');
		}
		else if($var['role_id']==2)
		{
			create_error('You cannot delete the new member role.');
		}
		$db->query('DELETE FROM alliance_has_roles
					WHERE game_id = '.$player->getGameID().' AND
						  alliance_id = '.$alliance_id.' AND
						  role_id = ' . $var['role_id']);
	// otherwise we update it
	}
	else
	{
		if ($_REQUEST['unlimited']) $withPerDay = -2;
		elseif ($_REQUEST['positive']) $withPerDay = -1;
		else $withPerDay = $_REQUEST['maxWith'];
		if (!is_numeric($withPerDay)) create_error('You must enter a number for max withdrawls per 24 hours.');
		$changePass = (bool)$_REQUEST['changePW'];
		$removeMember = (bool)$_REQUEST['removeMember'];
		$changeMOD = (bool)$_REQUEST['changeMoD'];
		$changeRoles = (bool)($var['role_id'] == 1 || $_REQUEST['changeRoles']); //Leader can always change roles.
		$planetAccess = (bool)$_REQUEST['planets'];
		$mbMessages = (bool)$_REQUEST['mbMessages'];
		$exemptWith = (bool)$_REQUEST['exemptWithdrawls'];
		$sendAllMsg = (bool)$_REQUEST['sendAllMsg'];
		$db->query('UPDATE alliance_has_roles
					SET role = ' . $db->escape_string($_POST['role'], true) . ',
					with_per_day = '.$withPerDay.',
					remove_member = '.$db->escapeBoolean($removeMember).',
					change_pass = '.$db->escapeBoolean($changePass).',
					change_mod = '.$db->escapeBoolean($changeMOD).',
					change_roles = '.$db->escapeBoolean($changeRoles).',
					planet_access = '.$db->escapeBoolean($planetAccess).',
					exempt_with = '.$db->escapeBoolean($exemptWith).',
					mb_messages = '.$db->escapeBoolean($mbMessages).',
					send_alliance_msg = '.$db->escapeBoolean($sendAllMsg).' 
					WHERE game_id = '.$player->getGameID().' AND
						  alliance_id = '.$alliance_id.' AND
						  role_id = ' . $var['role_id']);

	}

}
$container = create_container('skeleton.php', 'alliance_roles.php');
$container['alliance_id'] = $alliance_id;
forward($container);

?>