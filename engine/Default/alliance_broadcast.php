<?php

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $player->getAllianceID() . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',$player->getAllianceName() . ' (' . $player->getAllianceID() . ')');
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_alliance_menue($player->getAllianceID(),$db->getField('leader_id'));

$container = array();
$container['url'] = 'alliance_broadcast_processing.php';
$container['alliance_id'] = $var['alliance_id'];
$PHP_OUTPUT.= '<b>From: </b>';
$PHP_OUTPUT.= $player->getPlayerName() . '(' . $player->getPlayerID();
$PHP_OUTPUT.= ')<br /><b>To:</b> Whole Alliance<br /><br />';

$form = create_form($container,'Send Message');

$PHP_OUTPUT.= $form['form'];

$PHP_OUTPUT.= '<textarea name=\'message\'></textarea><br /><br />';

$PHP_OUTPUT.= $form['submit'];

$PHP_OUTPUT.= '</form>';

?>