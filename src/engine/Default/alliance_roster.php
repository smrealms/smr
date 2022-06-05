<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();
$player = $session->getPlayer();

$allianceID = $var['alliance_id'] ?? $player->getAllianceID();

$alliance = SmrAlliance::getAlliance($allianceID, $player->getGameID());
$template->assign('Alliance', $alliance);

$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

$varAction = $var['action'] ?? '';
// Does anyone actually use these?
$showRoles = $varAction == 'Show Alliance Roles';
$template->assign('ShowRoles', $showRoles);
if ($showRoles) {
	// initialize with text
	$roles = [];

	// get all roles from db for faster access later
	$dbResult = $db->read('SELECT role_id, role
				FROM alliance_has_roles
				WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
				AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
				ORDER BY role_id');
	foreach ($dbResult->records() as $dbRecord) {
		$roles[$dbRecord->getInt('role_id')] = $dbRecord->getField('role');
	}
	$template->assign('Roles', $roles);

	$container = Page::create('alliance_roles_save_processing.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('SaveAllianceRolesHREF', $container->href());
}


// If the alliance is the player's alliance they get live information
// Otherwise it comes from the cache.
$dbResult = $db->read('SELECT
	SUM(experience) AS alliance_xp,
	FLOOR(AVG(experience)) AS alliance_avg
	FROM player
	WHERE alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
	AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
	GROUP BY alliance_id');
$dbRecord = $dbResult->record();

$template->assign('AllianceExp', $dbRecord->getInt('alliance_xp'));
$template->assign('AllianceAverageExp', $dbRecord->getInt('alliance_avg'));

if ($account->getAccountID() == $alliance->getLeaderID() || $account->hasPermission(PERMISSION_EDIT_ALLIANCE_DESCRIPTION)) {
	$container = Page::create('alliance_stat.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('EditAllianceDescriptionHREF', $container->href());
}

$dbResult = $db->read('SELECT 1 FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
			AND role_id = ' . $db->escapeNumber($player->getAllianceRole()) . ' AND change_roles = \'TRUE\'');
$allowed = $dbResult->hasRecord();
$template->assign('CanChangeRoles', $allowed);

$alliancePlayers = $alliance->getMembers();
$template->assign('AlliancePlayers', $alliancePlayers);

if ($alliance->getAllianceID() == $player->getAllianceID()) {
	// Alliance members get to see active/inactive status of members
	$template->assign('ActiveIDs', $alliance->getActiveIDs());
	$container = Page::create('alliance_roster.php');
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
