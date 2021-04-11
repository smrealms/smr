<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();
$player = $session->getPlayer();

if (!isset($var['alliance_id'])) {
	$session->updateVar('alliance_id', $player->getAllianceID());
}

$alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('Alliance', $alliance);

$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

$varAction = $var['action'] ?? '';
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

	$container = Page::create('alliance_roles_save_processing.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('SaveAllianceRolesHREF', $container->href());
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

$db->requireRecord();
$template->assign('AllianceExp', $db->getInt('alliance_xp'));
$template->assign('AllianceAverageExp', $db->getInt('alliance_avg'));

if ($account->getAccountID() == $alliance->getLeaderID() || $account->hasPermission(PERMISSION_EDIT_ALLIANCE_DESCRIPTION)) {
	$container = Page::create('skeleton.php', 'alliance_stat.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('EditAllianceDescriptionHREF', $container->href());
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
	$container = Page::create('skeleton.php', 'alliance_roster.php');
	if ($showRoles) {
		$container['action'] = 'Hide Alliance Roles';
	} else {
		$container['action'] = 'Show Alliance Roles';
	}
	$template->assign('ToggleRolesHREF', $container->href());
}

// If the player is already in an alliance, we don't want to print
// any messages, so we simply omit the "join alliance" section.
$joinRestriction = $player->hasAlliance() ? true : $alliance->getJoinRestriction($player);
$template->assign('JoinRestriction', $joinRestriction);
if ($joinRestriction === false) {
	$container = Page::create('alliance_join_processing.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('JoinHREF', $container->href());
}
