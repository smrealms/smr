<?php

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $player->alliance_id . ' LIMIT 1');
$db->next_record();
print_topic($player->alliance_name . ' (' . $player->alliance_id . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($player->alliance_id,$db->f('leader_id'));

$container = array();
$container['url'] = 'alliance_leadership_processing.php';
$container['body'] = '';
$form = create_form($container,'Handover Leadership');

echo $form['form'];

echo 'Please select the new Leader:&nbsp;&nbsp;&nbsp;<select name="leader_id" size="1">';

$db->query('
SELECT account_id,player_id,player_name 
FROM player 
WHERE game_id=' . $player->game_id . '
AND alliance_id=' . $player->alliance_id //No limit in case they are over limit - ie NHA
);

while ($db->next_record()) {
	echo '<option value="' . $db->f('account_id') . '"';
	if ($db->f('account_id') == $player->account_id) echo ' selected="selected"';
	echo '>';
	echo stripslashes($db->f('player_name'));
	echo ' (';
	echo $db->f('player_id');
	echo ')</option>';
}

print("</select><br><br>");

echo $form['submit'];
echo '</form>';

?>