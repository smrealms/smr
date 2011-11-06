<?php
$db->query('SELECT 1 FROM draft_leaders WHERE game_id='.$db->escapeNumber($player->getGameID()).' AND account_id='.$db->escapeNumber($player->getAccountID()));
if($db->nextRecord())
{
	require_once(ENGINE.'Default/alliance_create_processing.php');
}
else
{
	create_error('You cannot create an alliance in a draft game.');
}
?>