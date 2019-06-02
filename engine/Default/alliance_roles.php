<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id', $player->getAllianceID());
}

$alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
Menu::alliance($alliance->getAllianceID(), $alliance->getLeaderID());

$db->query('SELECT * 
FROM alliance_has_roles
WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
ORDER BY role_id
');
$allianceRoles = array();
while ($db->nextRecord()) {
	$roleID = $db->getField('role_id');
	$allianceRoles[$roleID]['RoleID'] = $roleID;
	$allianceRoles[$roleID]['Name'] = $db->getField('role');
	$allianceRoles[$roleID]['EditingRole'] = isset($var['role_id']) && $var['role_id'] == $roleID;
	$allianceRoles[$roleID]['CreatingRole'] = false;
	if ($allianceRoles[$roleID]['EditingRole']) {
		$container = create_container('alliance_roles_processing.php');
		$allianceRoles[$roleID]['WithdrawalLimit'] = $db->getInt('with_per_day');
		$allianceRoles[$roleID]['PositiveBalance'] = $db->getBoolean('positive_balance');
		$allianceRoles[$roleID]['TreatyCreated'] = $db->getBoolean('treaty_created');
		$allianceRoles[$roleID]['RemoveMember'] = $db->getBoolean('remove_member');
		$allianceRoles[$roleID]['ChangePass'] = $db->getBoolean('change_pass');
		$allianceRoles[$roleID]['ChangeMod'] = $db->getBoolean('change_mod');
		$allianceRoles[$roleID]['ChangeRoles'] = $db->getBoolean('change_roles');
		$allianceRoles[$roleID]['PlanetAccess'] = $db->getBoolean('planet_access');
		$allianceRoles[$roleID]['ModerateMessageboard'] = $db->getBoolean('mb_messages');
		$allianceRoles[$roleID]['ExemptWithdrawals'] = $db->getBoolean('exempt_with');
		$allianceRoles[$roleID]['SendAllianceMessage'] = $db->getBoolean('send_alliance_msg');
		$allianceRoles[$roleID]['OpLeader'] = $db->getBoolean('op_leader');
		$allianceRoles[$roleID]['ViewBondsInPlanetList'] = $db->getBoolean('view_bonds');
	} else {
		$container = create_container('skeleton.php', 'alliance_roles.php');
	}
	$container['role_id'] = $roleID;
	$container['alliance_id'] = $alliance->getAllianceID();
	$allianceRoles[$roleID]['HREF'] = SmrSession::getNewHREF($container);
}
$template->assign('AllianceRoles', $allianceRoles);
$container = create_container('alliance_roles_processing.php');
$container['alliance_id'] = $alliance->getAllianceID();

$template->assign('CreateRole', array(
	'HREF' => SmrSession::getNewHREF($container),
	'RoleID' => '',
	'Name' => '',
	'CreatingRole' => true,
	'EditingRole' => true,
	'WithdrawalLimit' => 0,
	'PositiveBalance' => false,
	'TreatyCreated' => false,
	'RemoveMember' => false,
	'ChangePass' => false,
	'ChangeMod' => false,
	'ChangeRoles' => false,
	'PlanetAccess' => true,
	'ModerateMessageboard' => false,
	'ExemptWithdrawals' => false,
	'SendAllianceMessage' => false,
	'OpLeader' => false,
	'ViewBondsInPlanetList' => false));
