<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
$db->query('SELECT leader_id,`mod`,img_src, alliance_name, alliance_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
$leader_id = $db->f("leader_id");
print_topic(stripslashes($db->f("alliance_name")) . ' (' . $db->f("alliance_id") . ')');
//print_topic($player->alliance_name . ' (' . $player->alliance_id . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($alliance_id,$db->f('leader_id'));

echo '<div align="center">';

if (strlen($db->f('img_src')) && $db->f('img_src') != 'http://') {
	echo '<img class="alliance" src="';
	echo $db->f('img_src');
	echo '" alt="' . stripslashes($db->f('alliance_name')) . ' Banner"><br><br>';
}

echo '<span class="yellow">Message from your leader</span><br><br>';
echo $db->f('mod');

$db->query("SELECT * FROM player_has_alliance_role WHERE account_id = $player->account_id AND game_id = $player->game_id AND alliance_id=$player->alliance_id");
if ($db->next_record()) $role_id = $db->f("role_id");
else $role_id = 0;
$db->query("SELECT * FROM alliance_has_roles WHERE alliance_id = $player->alliance_id AND game_id = $player->game_id AND role_id = $role_id");
$db->next_record();
if ($db->f("change_mod") || $db->f("change_pass")) {
	echo '<br><br>';
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_stat.php';
	$container['alliance_id'] = $alliance_id;
	print_button($container,'Edit');
}
echo '</div>';

?>