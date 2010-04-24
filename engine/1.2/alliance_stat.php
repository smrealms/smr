<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
$db->query('SELECT leader_id,img_src,alliance_password,alliance_description,`mod`,alliance_name,alliance_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
$pw = $db->f('alliance_password');
$desc = strip_tags($db->f('alliance_description'));
$img = $db->f('img_src');
$mod = strip_tags($db->f('mod'));
print_topic(stripslashes($db->f("alliance_name")) . ' (' . $db->f("alliance_id") . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($alliance_id,$db->f('leader_id'));

$container=array();
$container['url'] = 'alliance_stat_processing.php';
$container['body'] = '';
$container['alliance_id'] = $alliance_id;

$form = create_form($container,'Change');
$db->query("SELECT * FROM player_has_alliance_role WHERE account_id = $player->account_id AND game_id = $player->game_id AND alliance_id=$player->alliance_id");
if ($db->next_record()) $role_id = $db->f("role_id");
else $role_id = 0;
$db->query("SELECT * FROM alliance_has_roles WHERE alliance_id = $alliance_id AND game_id = $player->game_id AND role_id = $role_id");
$db->next_record();
echo $form['form'];

//print_form(create_container("alliance_stat_processing.php", ""));
echo '<table cellspacing="0" cellpadding="0" class="nobord nohpad">';

if ($db->f("change_pass")) {
	echo '<tr><td class="top">Password:&nbsp;</td><td><input type="password" name="password" size="30" value="';
	echo $pw;
	echo '"></td></tr>';
} if ($db->f("change_mod")) {
	echo '<tr><td class="top">Description:&nbsp;</td><td><textarea name="description">';
	echo $desc;
	echo '</textarea></td></tr>';
	
	echo '<tr><td class="top">Image URL:&nbsp;</td><td><input type="text" name="url" size="30" value="';
	echo $img;
	echo '"></td></tr>';
	
	echo '<tr><td class="top">Message Of The Day:&nbsp;</td><td><textarea name="mod">';
	echo $mod;
	echo '</textarea></td></tr>';
}
echo '</table><br />';
echo $form['submit'];
echo '</form>';

?>