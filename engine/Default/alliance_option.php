<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}

$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

// Create an array of links with descriptions
$links = array();

$container = create_container('skeleton.php', 'alliance_leave_confirm.php');
$links[] = array(
	'link' => create_link($container, 'Leave Alliance'),
	'text' => 'Leave the alliance. Alliance leaders must hand over leadership before leaving.',
);

$container = create_container('alliance_share_maps_processing.php');
$links[] = array(
	'link' => create_link($container, 'Share Maps'),
	'text' => 'Share your knowledge of the universe with your alliance mates.',
);

$role_id = $player->getAllianceRole($alliance->getAllianceID());

$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
$db->nextRecord();

$container['url'] = 'skeleton.php';
$container['alliance_id'] = $alliance->getAllianceID();

if ($db->getBoolean('remove_member')) {
	$container['body'] = 'alliance_remove_member.php';
	$links[] = array(
		'link' => create_link($container, 'Remove Member'),
		'text' => 'Remove a trader from alliance roster.',
	);
}
if ($player->isAllianceLeader()) {
	$container['body'] = 'alliance_leadership.php';
	$links[] = array(
		'link' => create_link($container, 'Handover Leadership'),
		'text' => 'Hand over leadership of the alliance to an alliance mate.',
	);
}
if ($db->getBoolean('change_pass') || $db->getBoolean('change_mod')) {
	$container['body'] = 'alliance_stat.php';
	$links[] = array(
		'link' => create_link($container, 'Change Alliance Stats'),
		'text' => 'Change the password, description or message of the day for the alliance.',
	);
}
if ($db->getBoolean('change_roles')) {
	$container['body'] = 'alliance_roles.php';
	$links[] = array(
		'link' => create_link($container, 'Define Alliance Roles'),
		'text' => 'Each member in your alliance can fit into a specific role, a task. Here you can define the roles that you can assign to them.',
	);
}
if ($db->getBoolean('exempt_with')) {
	$container['body'] = 'alliance_exempt_authorize.php';
	$links[] = array(
		'link' => create_link($container, 'Exempt Bank Transactions'),
		'text' => 'Here you can set certain alliance account transactions as exempt. This makes them not count against, or for, the player making the transaction in the bank report.',
	);
}
if ($db->getBoolean('treaty_entry')) {
	$container['body'] = 'alliance_treaties.php';
	$links[] = array(
		'link' => create_link($container, 'Negotiate Treaties'),
		'text' => 'Negotitate treaties with other alliances.',
	);
}
if ($player->isAllianceLeader()) {
	$container['body'] = 'alliance_set_op.php';
	$links[] = array(
		'link' => create_link($container, 'Schedule Operation'),
		'text' => 'Schedule and manage the next alliance operation.',
	);
}

$template->assign('Links', $links);
