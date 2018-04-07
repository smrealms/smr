<?php

require_once(get_file_loc('SmrAlliance.class.inc'));

if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}

$alliance =& SmrAlliance::getAlliance($var['alliance_id'],$player->getGameID());
$template->assign('Alliance', $alliance);

$template->assign('PageTopic', $alliance->getAllianceName(false, true));
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$varAction = isset($var['action']) ? $var['action'] : '';
// Does anyone actually use these?
$showRoles = $varAction == 'Show Alliance Roles';
$template->assign('ShowRoles', $showRoles);
if ($showRoles) {
	// initialize with text
	$roles = array();

	// get all roles from db for faster access later
	$db->query('SELECT role_id, role
				FROM alliance_has_roles
				WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
				AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
				ORDER BY role_id');
	while ($db->nextRecord()) {
		$roles[$db->getInt('role_id')] = $db->getField('role');
	}
	$template->assign('Roles', $roles);

	$container=create_container('alliance_roles_save_processing.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('SaveAllianceRolesHREF', SmrSession::getNewHREF($container));
}


// If the alliance is the player's alliance they get live information
// Otherwise it comes from the cache.
$db->query('SELECT
	SUM(experience) AS alliance_xp,
	FLOOR(AVG(experience)) AS alliance_avg
	FROM player
	WHERE alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
	AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
	GROUP BY alliance_id'
);

$db->nextRecord();
$template->assign('AllianceExp', $db->getInt('alliance_xp'));
$template->assign('AllianceAverageExp', $db->getInt('alliance_avg'));

if($account->getAccountID() == $alliance->getLeaderID() || $account->hasPermission(PERMISSION_EDIT_ALLIANCE_DESCRIPTION)) {
	$container = create_container('skeleton.php', 'alliance_stat.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('EditAllianceDescriptionHREF', SmrSession::getNewHREF($container));
}

$db->query('SELECT 1 FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
			AND role_id = ' . $db->escapeNumber($player->getAllianceRole()) . ' AND change_roles = \'TRUE\'');
$allowed = $db->nextRecord();
$template->assign('CanChangeRoles', $allowed);

$alliancePlayers = $alliance->getMembers();
$template->assign('AlliancePlayers', $alliancePlayers);

if ($alliance->getAllianceID() == $player->getAllianceID()) {
	// Alliance members get to see active/inactive status of members
	$template->assign('ActiveIDs', $alliance->getActiveIDs());
	$container = create_container('skeleton.php', 'alliance_roster.php');
	if($showRoles) {
		$container['action'] = 'Hide Alliance Roles';
	}
	else {
		$container['action'] = 'Show Alliance Roles';
	}
	$template->assign('ToggleRolesHREF', SmrSession::getNewHREF($container));
}

$canJoin = $alliance->canJoinAlliance($player);
$template->assign('CanJoin', $canJoin);
if ($canJoin === true) {
	$container = create_container('alliance_join_processing.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('JoinHREF', SmrSession::getNewHREF($container));
}

?>
