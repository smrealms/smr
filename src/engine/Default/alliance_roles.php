<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (!isset($var['alliance_id'])) {
	$session->updateVar('alliance_id', $player->getAllianceID());
}

$alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT *
FROM alliance_has_roles
WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
ORDER BY role_id
');
$allianceRoles = array();
foreach ($dbResult->records() as $dbRecord) {
	$roleID = $dbRecord->getInt('role_id');
	$allianceRoles[$roleID]['RoleID'] = $roleID;
	$allianceRoles[$roleID]['Name'] = $dbRecord->getField('role');
	$allianceRoles[$roleID]['EditingRole'] = isset($var['role_id']) && $var['role_id'] == $roleID;
	$allianceRoles[$roleID]['CreatingRole'] = false;
	if ($allianceRoles[$roleID]['EditingRole']) {
		$container = Page::create('alliance_roles_processing.php');
		$allianceRoles[$roleID]['WithdrawalLimit'] = $dbRecord->getInt('with_per_day');
		$allianceRoles[$roleID]['PositiveBalance'] = $dbRecord->getBoolean('positive_balance');
		$allianceRoles[$roleID]['TreatyCreated'] = $dbRecord->getBoolean('treaty_created');
		$allianceRoles[$roleID]['RemoveMember'] = $dbRecord->getBoolean('remove_member');
		$allianceRoles[$roleID]['ChangePass'] = $dbRecord->getBoolean('change_pass');
		$allianceRoles[$roleID]['ChangeMod'] = $dbRecord->getBoolean('change_mod');
		$allianceRoles[$roleID]['ChangeRoles'] = $dbRecord->getBoolean('change_roles');
		$allianceRoles[$roleID]['PlanetAccess'] = $dbRecord->getBoolean('planet_access');
		$allianceRoles[$roleID]['ModerateMessageboard'] = $dbRecord->getBoolean('mb_messages');
		$allianceRoles[$roleID]['ExemptWithdrawals'] = $dbRecord->getBoolean('exempt_with');
		$allianceRoles[$roleID]['SendAllianceMessage'] = $dbRecord->getBoolean('send_alliance_msg');
		$allianceRoles[$roleID]['OpLeader'] = $dbRecord->getBoolean('op_leader');
		$allianceRoles[$roleID]['ViewBondsInPlanetList'] = $dbRecord->getBoolean('view_bonds');
	} else {
		$container = Page::create('skeleton.php', 'alliance_roles.php');
	}
	$container['role_id'] = $roleID;
	$container['alliance_id'] = $alliance->getAllianceID();
	$allianceRoles[$roleID]['HREF'] = $container->href();
}
$template->assign('AllianceRoles', $allianceRoles);
$container = Page::create('alliance_roles_processing.php');
$container['alliance_id'] = $alliance->getAllianceID();

$template->assign('CreateRole', array(
	'HREF' => $container->href(),
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
