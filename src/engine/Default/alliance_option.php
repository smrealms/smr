<?php declare(strict_types=1);

use Smr\Database;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$allianceID = $var['alliance_id'] ?? $player->getAllianceID();

$alliance = SmrAlliance::getAlliance($allianceID, $player->getGameID());
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

// Create an array of links with descriptions
$links = [];

$isDraftGame = $player->getGame()->isGameType(SmrGame::GAME_TYPE_DRAFT);

if ($isDraftGame && $player->isDraftLeader()) {
	// Draft leaders get to pick members
	$container = Page::create('alliance_pick.php');
	$links[] = [
		'link' => create_link($container, 'Draft Members'),
		'text' => 'Choose members to join your alliance.',
	];
}

if (!$isDraftGame) {
	// Players can choose to leave their alliance (except in Draft games)
	$container = Page::create('alliance_leave_confirm.php');
	$links[] = [
		'link' => create_link($container, 'Leave Alliance'),
		'text' => 'Leave the alliance. Alliance leaders must hand over leadership before leaving.',
	];
}

$container = Page::create('alliance_share_maps_processing.php');
$links[] = [
	'link' => create_link($container, 'Share Maps'),
	'text' => 'Share your knowledge of the universe with your alliance mates.',
];

$role_id = $player->getAllianceRole($alliance->getAllianceID());

$db = Database::getInstance();
$dbResult = $db->read('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
$dbRecord = $dbResult->record();

if ($dbRecord->getBoolean('change_pass')) {
	$container = Page::create('alliance_invite_player.php');
	$links[] = [
		'link' => create_link($container, 'Invite Player'),
		'text' => 'Invite a player to the alliance.',
	];
}
if ($dbRecord->getBoolean('remove_member')) {
	$container = Page::create('alliance_remove_member.php');
	$links[] = [
		'link' => create_link($container, 'Remove Member'),
		'text' => 'Remove a trader from alliance roster.',
	];
}
if ($player->isAllianceLeader()) {
	$container = Page::create('alliance_leadership.php');
	$links[] = [
		'link' => create_link($container, 'Handover Leadership'),
		'text' => 'Hand over leadership of the alliance to an alliance mate.',
	];
}
if ($dbRecord->getBoolean('change_pass') || $dbRecord->getBoolean('change_mod')) {
	$container = Page::create('alliance_stat.php', ['alliance_id' => $alliance->getAllianceID()]);
	$links[] = [
		'link' => create_link($container, 'Change Alliance Stats'),
		'text' => 'Change the password, description or message of the day for the alliance.',
	];
}
if ($dbRecord->getBoolean('change_roles')) {
	$container = Page::create('alliance_roles.php', ['alliance_id' => $alliance->getAllianceID()]);
	$links[] = [
		'link' => create_link($container, 'Define Alliance Roles'),
		'text' => 'Each member in your alliance can fit into a specific role, a task. Here you can define the roles that you can assign to them.',
	];
}
if ($dbRecord->getBoolean('exempt_with')) {
	$container = Page::create('alliance_exempt_authorize.php');
	$links[] = [
		'link' => create_link($container, 'Exempt Bank Transactions'),
		'text' => 'Here you can set certain alliance account transactions as exempt. This makes them not count against, or for, the player making the transaction in the bank report.',
	];
}
if ($dbRecord->getBoolean('treaty_entry')) {
	$container = Page::create('alliance_treaties.php');
	$links[] = [
		'link' => create_link($container, 'Negotiate Treaties'),
		'text' => 'Negotitate treaties with other alliances.',
	];
}
if ($dbRecord->getBoolean('op_leader')) {
	$container = Page::create('alliance_set_op.php');
	$links[] = [
		'link' => create_link($container, 'Schedule Operation'),
		'text' => 'Schedule and manage the next alliance operation and designate an alliance flagship.',
	];
}

$template->assign('Links', $links);
