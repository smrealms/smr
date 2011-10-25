<?php
include(ENGINE.'Default/alliance_option.php');

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . $player->getAllianceID() . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->nextRecord();
$leader_id = $db->getField('leader_id');

if($player->getAccountID()==$leader_id)
{
	$PHP_OUTPUT.= '<br /><br /><b><big>';
	$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'alliance_pick.php',array('alliance_id'=>$alliance_id)),'Pick Members');
	$PHP_OUTPUT.= '</big></b><br />Pick alliance members.';
}
?>