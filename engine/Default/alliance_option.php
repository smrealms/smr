<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}

$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$container=create_container('skeleton.php', 'alliance_leave_confirm.php');
$PHP_OUTPUT.= '<b><big>';
$PHP_OUTPUT.=create_link($container,'Leave Alliance');
$PHP_OUTPUT.= '</big></b>';

$PHP_OUTPUT.= '<br />Leave the alliance. Alliance leaders must hand over leadership before leaving.<br /><br /><b><big>';
$container=create_container('alliance_share_maps_processing.php');
$PHP_OUTPUT.=create_link($container,'Share Maps');
$PHP_OUTPUT.= '</big></b>';
$PHP_OUTPUT.= '<br />Share your knowledge of the universe with your alliance mates.';

$role_id = $player->getAllianceRole($alliance->getAllianceID());

$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
$db->nextRecord();

$container['url'] = 'skeleton.php';
$container['alliance_id'] = $alliance->getAllianceID();
if ($db->getBoolean('remove_member')) {
	$PHP_OUTPUT.= '<br /><br />';
	$PHP_OUTPUT.='<b><big>';
	$container['body'] = 'alliance_remove_member.php';
	$PHP_OUTPUT.=create_link($container,'Remove Member');
	$PHP_OUTPUT.= '</big></b>';
	$PHP_OUTPUT.= '<br />Remove a trader from alliance roster.';
}
if ($player->getAccountID() == $alliance->getLeaderID()) {
	$PHP_OUTPUT.= '<br /><br /><b><big>';
	$container['body'] = 'alliance_leadership.php';
	$PHP_OUTPUT.=create_link($container,'Handover Leadership');
	$PHP_OUTPUT.= '</big></b><br />Hand over leadership of the alliance to an alliance mate.';
}
if ($db->getBoolean('change_pass') || $db->getBoolean('change_mod')) {
	$PHP_OUTPUT.= '<br /><br /><b><big>';
	$container['body'] = 'alliance_stat.php';
	$PHP_OUTPUT.=create_link($container,'Change Alliance Stats');
	$PHP_OUTPUT.= '</big></b><br />Change the password, description or message of the day for the alliance.';
}
if ($db->getBoolean('change_roles')) {
	$PHP_OUTPUT.= '<br /><br /><b><big>';
	$container['body'] = 'alliance_roles.php';
	$PHP_OUTPUT.=create_link($container,'Define Alliance Roles');
	$PHP_OUTPUT.= '</big></b><br />Each member in your alliance can fit into a specific role, a task. Here you can define the roles that you can assign to them.';
}
if ($db->getBoolean('exempt_with')) {
	$PHP_OUTPUT.= '<br /><br /><b><big>';
	$container['body'] = 'alliance_exempt_authorize.php';
	$PHP_OUTPUT.=create_link($container,'Exempt Bank Transactions');
	$PHP_OUTPUT.= '</big></b><br />Here you can set certain alliance account transactions as exempt. This makes them not count against, or for, the player making the transaction in the bank report.';
}
if ($db->getBoolean('treaty_entry')) {
	$PHP_OUTPUT.= '<br /><br /><b><big>';
	$container['body'] = 'alliance_treaties.php';
	$PHP_OUTPUT.=create_link($container,'Negotiate Treaties');
	$PHP_OUTPUT.= '</big></b><br />Negotitate treaties with other alliances.';
}
?>