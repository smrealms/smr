<?php

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $player->alliance_id . ' LIMIT 1');
$db->next_record();
print_topic($player->alliance_name . ' (' . $player->alliance_id . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($player->alliance_id,$db->f('leader_id'));

echo 'Do you really want to leave this alliance?<br><br>';

$container = array();
$container['url'] = 'alliance_leave_processing.php';
$container['body'] = '';
$container['action'] = 'YES';

print_button($container,'Yes!');
$container['action'] = 'NO';
echo '&nbsp;&nbsp;&nbsp;';
print_button($container,'No!');

?>