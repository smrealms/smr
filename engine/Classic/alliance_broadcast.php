<?php

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $player->alliance_id . ' LIMIT 1');
$db->next_record();
print_topic($player->alliance_name . ' (' . $player->alliance_id . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($player->alliance_id,$db->f('leader_id'));

$container = array();
$container['url'] = 'alliance_broadcast_processing.php';
$container['alliance_id'] = $var['alliance_id'];
echo '<b>From: </b>';
echo $player->player_name . '(' . $player->player_id;
echo ')<br><b>To:</b> Whole Alliance<br><br>';

$form = create_form($container,'Send Message');

echo $form['form'];

echo '<textarea name="message"></textarea><br><br>';

echo $form['submit'];

echo '</form>';

?>