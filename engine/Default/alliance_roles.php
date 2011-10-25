<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
$db->query('SELECT leader_id,alliance_id,alliance_name FROM alliance WHERE game_id=' . $player->getGameID() . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',stripslashes($db->getField('alliance_name')) . ' (' . $db->getField('alliance_id') . ')');
include(get_file_loc('menue.inc'));
create_alliance_menue($alliance_id,$db->getField('leader_id'));

$db->query('SELECT * 
FROM alliance_has_roles
WHERE game_id=' . $player->getGameID() . '
AND alliance_id=' . $alliance_id .'
ORDER BY role_id
');
$allianceRoles = array();
while ($db->nextRecord())
{
	$roleID = $db->getField('role_id');
	$allianceRoles[$roleID]['RoleID'] = $roleID;
	$allianceRoles[$roleID]['Name'] = $db->getField('role');
	$allianceRoles[$roleID]['EditingRole'] = $var['role_id'] == $roleID;
	if ($allianceRoles[$roleID]['EditingRole'])
	{
		$container = create_container('alliance_roles_processing.php');
		$allianceRoles[$roleID]['WithdrawalLimit'] = $db->getInt('with_per_day');
		$allianceRoles[$roleID]['TreatyCreated'] = $db->getBoolean('treaty_created');
		$allianceRoles[$roleID]['RemoveMember'] = $db->getBoolean('remove_member');
		$allianceRoles[$roleID]['ChangePass'] = $db->getBoolean('change_pass');
		$allianceRoles[$roleID]['ChangeMod'] = $db->getBoolean('change_mod');
		$allianceRoles[$roleID]['ChangeRoles'] = $db->getBoolean('change_roles');
		$allianceRoles[$roleID]['PlanetAccess'] = $db->getBoolean('planet_access');
		$allianceRoles[$roleID]['ModerateMessageboard'] = $db->getBoolean('mb_messages');
		$allianceRoles[$roleID]['ExemptWithdrawals'] = $db->getBoolean('exempt_with');
		$allianceRoles[$roleID]['SendAllianceMessage'] = $db->getBoolean('send_alliance_msg');
	}
	else
	{
		$container = create_container('skeleton.php', 'alliance_roles.php');
		$form = create_form($container,'Edit');
		$PHP_OUTPUT.= $form['form'];
		$PHP_OUTPUT.= '<input type="text" name="role" value="' . htmlspecialchars($db->getField('role')) . '" maxlength="32">&nbsp;&nbsp;';
		$PHP_OUTPUT.= $form['submit'];
		$PHP_OUTPUT.= '</form><br />';
	}
	$container['role_id'] = $roleID;
	$container['alliance_id'] = $alliance_id;
	$allianceRoles[$roleID]['HREF'] = SmrSession::get_new_href($container);
}
$template->assignByRef('AllianceRoles',$allianceRoles);
$container = create_container('alliance_roles_processing.php');
$container['alliance_id'] = $alliance_id;

$template->assign('CreateRole', array(
	'HREF' => SmrSession::get_new_href($container),
	'CreatingRole' => true,
	'EditingRole' => true,
	'WithdrawalLimit' => 0,
	'TreatyCreated' => false,
	'RemoveMember' => false,
	'ChangePass' => false,
	'ChangeMod' => false,
	'ChangeRoles' => false,
	'PlanetAccess' => true,
	'ModerateMessageboard' => false,
	'ExemptWithdrawals' => false,
	'SendAllianceMessage' => false));
?>