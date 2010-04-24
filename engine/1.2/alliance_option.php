<?php

if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
$db->query('SELECT leader_id, alliance_name, alliance_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
$leader_id = $db->f("leader_id");
print_topic(stripslashes($db->f("alliance_name")) . ' (' . $db->f("alliance_id") . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($alliance_id,$db->f('leader_id'));

$container=array();
$container['url'] = 'skeleton.php';
echo '<b><big>';
$container['body'] = 'alliance_leave_confirm.php';
print_link($container,'Leave Alliance');
echo '</big></b>';

echo '<br>Leave the alliance. Alliance leaders must hand over leadership before leaving.<br><br><b><big>';
$container['url'] = 'alliance_share_maps_processing.php';
$container['body'] = '';
print_link($container,'Share Maps');
echo '</big></b>';
echo '<br>Share your knowledge of the universe with your alliance mates.';
$db->query("SELECT * FROM player_has_alliance_role WHERE account_id = $player->account_id AND game_id = $player->game_id AND alliance_id=$player->alliance_id");
if ($db->next_record()) $role_id = $db->f("role_id");
else $role_id = 0;
$db->query("SELECT * FROM alliance_has_roles WHERE alliance_id = $player->alliance_id AND game_id = $player->game_id AND role_id = $role_id");
$db->next_record();

$container['url'] = 'skeleton.php';
$container['alliance_id'] = $alliance_id;
if ($db->f("remove_member")) {
	echo '<br><br>';
	echo'<b><big>';
	$container['body'] = 'alliance_remove_member.php';
	print_link($container,'Remove Member');
	echo '</big></b>';
	echo '<br>Remove a trader from alliance roster.';
} if ($player->account_id == $leader_id) {
	echo '<br><br><b><big>';
	$container['body'] = 'alliance_leadership.php';
	print_link($container,'Handover Leadership');
	echo '</big></b><br>Hand over leadership of the alliance to an alliance mate.';
} if ($db->f("change_pass") || $db->f("change_mod")) {
	echo '<br><br><b><big>';
	$container['body'] = 'alliance_stat.php';
	print_link($container,'Change Alliance Stats');
	echo '</big></b><br>Change the password, description or message of the day for the alliance.';
} if ($db->f("change_roles")) {
	echo '<br><br><b><big>';
	$container['body'] = 'alliance_roles.php';
	print_link($container,'Define Alliance Roles');
	echo '</big></b><br>Each member in your alliance can fit into a specific role, a task. Here you can define the roles that you can assign to them.';
} if ($db->f("exempt_with")) {
	echo '<br><br><b><big>';
	$container['body'] = 'alliance_exempt_authorize.php';
	print_link($container,'Exempt Bank Transactions');
	echo '</big></b><br>Here you can set certain alliance account transactions as exempt.  This makes them not count against, or for, the player making the transaction in the bank report.';
} if ($db->f("treaty_entry")) {
	echo '<br><br><b><big>';
	$container['body'] = 'alliance_treaties.php';
	print_link($container,'Negotiate Treaties');
	echo '</big></b><br>Negotitate treaties with other alliances.';
}
?>