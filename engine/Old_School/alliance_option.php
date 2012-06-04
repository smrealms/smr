<?

if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = '.$player->getAllianceID().';
$db->query('SELECT leader_id, alliance_name, alliance_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
$leader_id = $db->f('leader_id');
$smarty->assign('PageTopic',stripslashes($db->f('alliance_name')) . ' (' . $db->f('alliance_id') . ')');
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_alliance_menue($alliance_id,$db->f('leader_id'));

$container=array();
$container['url'] = 'skeleton.php';
$PHP_OUTPUT.= '<b><big>';
$container['body'] = 'alliance_leave_confirm.php';
$PHP_OUTPUT.=create_link($container,'Leave Alliance');
$PHP_OUTPUT.= '</big></b>';

$PHP_OUTPUT.= '<br>Leave the alliance. Alliance leaders must hand over leadership before leaving.<br><br><b><big>';
$container['url'] = 'alliance_share_maps_processing.php';
$container['body'] = '';
$PHP_OUTPUT.=create_link($container,'Share Maps');
$PHP_OUTPUT.= '</big></b>';
$PHP_OUTPUT.= '<br>Share your knowledge of the universe with your alliance mates.';
$db->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
if ($db->next_record()) $role_id = $db->f('role_id');
else $role_id = 0;
$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND role_id = '.$role_id);
$db->next_record();

$container['url'] = 'skeleton.php';
$container['alliance_id'] = $alliance_id;
if ($db->f('remove_member')) {
	$PHP_OUTPUT.= '<br><br>';
	$PHP_OUTPUT.='<b><big>';
	$container['body'] = 'alliance_remove_member.php';
	$PHP_OUTPUT.=create_link($container,'Remove Member');
	$PHP_OUTPUT.= '</big></b>';
	$PHP_OUTPUT.= '<br>Remove a trader from alliance roster.';
} if ($player->getAccountID() == $leader_id) {
	$PHP_OUTPUT.= '<br><br><b><big>';
	$container['body'] = 'alliance_leadership.php';
	$PHP_OUTPUT.=create_link($container,'Handover Leadership');
	$PHP_OUTPUT.= '</big></b><br>Hand over leadership of the alliance to an alliance mate.';
} if ($db->f('change_pass') || $db->f('change_mod')) {
	$PHP_OUTPUT.= '<br><br><b><big>';
	$container['body'] = 'alliance_stat.php';
	$PHP_OUTPUT.=create_link($container,'Change Alliance Stats');
	$PHP_OUTPUT.= '</big></b><br>Change the password, description or message of the day for the alliance.';
} if ($db->f('change_roles')) {
	$PHP_OUTPUT.= '<br><br><b><big>';
	$container['body'] = 'alliance_roles.php';
	$PHP_OUTPUT.=create_link($container,'Define Alliance Roles');
	$PHP_OUTPUT.= '</big></b><br>Each member in your alliance can fit into a specific role, a task. Here you can define the roles that you can assign to them.';
} if ($db->f('exempt_with')) {
	$PHP_OUTPUT.= '<br><br><b><big>';
	$container['body'] = 'alliance_exempt_authorize.php';
	$PHP_OUTPUT.=create_link($container,'Exempt Bank Transactions');
	$PHP_OUTPUT.= '</big></b><br>Here you can set certain alliance account transactions as exempt.  This makes them not count against, or for, the player making the transaction in the bank report.';
} if ($db->f('treaty_entry')) {
	$PHP_OUTPUT.= '<br><br><b><big>';
	$container['body'] = 'alliance_treaties.php';
	$PHP_OUTPUT.=create_link($container,'Negotiate Treaties');
	$PHP_OUTPUT.= '</big></b><br>Negotitate treaties with other alliances.';
}
?>