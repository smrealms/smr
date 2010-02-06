<?php

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $player->getAllianceID() . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',$player->getAllianceName() . ' (' . $player->getAllianceID() . ')');
include(get_file_loc('menue.inc'));
create_alliance_menue($player->getAllianceID(),$db->getField('leader_id'));

$PHP_OUTPUT.= 'Do you really want to leave this alliance?<br /><br />';

$container = array();
$container['url'] = 'alliance_leave_processing.php';
$container['body'] = '';
$container['action'] = 'YES';

$PHP_OUTPUT.=create_button($container,'Yes!');
$container['action'] = 'NO';
$PHP_OUTPUT.= '&nbsp;&nbsp;&nbsp;';
$PHP_OUTPUT.=create_button($container,'No!');

?>