<?php
require(ENGINE.'Default/alliance_option.php');

if($player->getAccountID()==$alliance->getLeaderID())
{
	$PHP_OUTPUT.= '<br /><br /><b><big>';
	$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'alliance_pick.php',array('alliance_id'=>$alliance->getAllianceID())),'Pick Members');
	$PHP_OUTPUT.= '</big></b><br />Pick alliance members.';
}
?>