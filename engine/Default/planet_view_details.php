<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);
		require_once(get_file_loc('SmrPlanet.class.inc'));
$smarty->assign('PageTopic','VIEWING PLANET DETAILS');
$db2 = new SmrMySqlDatabase();
if ($player->getAllianceID() != 0)
	$db->query('SELECT planet.sector_id as sector, player.game_id as game, time_attack, attacker_damage, planet_damage, trigger_id FROM player, planet, planet_attack WHERE player.game_id = planet.game_id AND ' .
											  'owner_id = account_id AND ' .
											  'player.game_id = '.$player->getGameID().' AND ' .
											  'planet.game_id = '.$player->getGameID().' AND ' .
                                              'planet.sector_id = planet_attack.sector_id AND ' .
                                              'planet.game_id = planet_attack.game_id AND ' .
											  'alliance_id = '.$player->getAllianceID().' ' .
										'ORDER BY time_attack DESC');
else
	$db->query('SELECT planet.sector_id as sector, player.game_id as game, time_attack, attacker_damage, planet_damage, trigger_id FROM player, planet, planet_attack WHERE player.game_id = planet.game_id AND ' .
    										'owner_id = account_id AND ' .
                                            'planet.game_id = '.$player->getGameID().' AND ' .
                                            'planet.sector_id = planet_attack.sector_id AND ' .
                                            'planet.game_id = planet_attack.game_id AND ' .
                                            'player.game_id = '.$player->getGameID().' ' .
                                      'ORDER BY time_attack DESC');

while ($db->next_record()) {

	$game_id = $db->f('game');
    $sector_id = $db->f('sector');
	$time = $db->f('time_attack');
    $attacker =& SmrPlayer::getPlayer($db->f('trigger_id'), $player->getGameID());
	$att_damage = $db->f('attacker_damage');
	$planet_damage = $db->f('planet_damage');
	$planet =& SmrPlanet::getPlanet($player->getGameID(),$db->f('sector'));
	$PHP_OUTPUT.=('Planet <span style=font-variant:small-caps>'.$planet->planet_name.'</span> is under attack by ' . $attacker->get_colored_name() . '<br />');
	$PHP_OUTPUT.=('This shot was at ' . date('n/j/Y g:i:s A', $time) . '.  The attacking team did '.$att_damage.' damage ');
	$PHP_OUTPUT.=('while the planet did '.$planet_damage.' damage<br /><br />');

}

?>