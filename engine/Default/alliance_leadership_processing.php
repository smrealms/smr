<?
$leader_id = $_REQUEST['leader_id'];
$db->query('UPDATE alliance SET leader_id = '.$leader_id .
							' WHERE alliance_id = '.$player->getAllianceID().' AND ' .
								  'game_id = '.$player->getGameID());
$db->query('UPDATE player_has_alliance_role SET role_id = 2 WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID().' AND alliance_id='.$player->getAllianceID());
$db->query('UPDATE player_has_alliance_role SET role_id = 1 WHERE account_id = '.$leader_id.' AND game_id = '.$player->getGameID().' AND alliance_id='.$player->getAllianceID());
forward(create_container('skeleton.php', 'alliance_roster.php'));

?>