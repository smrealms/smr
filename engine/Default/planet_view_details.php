<?php
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());
		require_once(get_file_loc('SmrPlanet.class.inc'));
$template->assign('PageTopic','Viewing Planet Details');
$db2 = new SmrMySqlDatabase();
if ($player->hasAlliance())
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

while ($db->nextRecord()) {

	$game_id = $db->getField('game');
    $sector_id = $db->getField('sector');
	$time = $db->getField('time_attack');
    $attacker =& SmrPlayer::getPlayer($db->getField('trigger_id'), $player->getGameID());
	$att_damage = $db->getField('attacker_damage');
	$planet_damage = $db->getField('planet_damage');
	$planet =& SmrPlanet::getPlanet($player->getGameID(),$db->getField('sector'));
	$PHP_OUTPUT.=('Planet <span style=font-variant:small-caps>'.$planet->getName().'</span> is under attack by ' . $attacker->get_colored_name() . '<br />');
	$PHP_OUTPUT.=('This shot was at ' . date(DATE_FULL_SHORT, $time) . '.  The attacking team did '.$att_damage.' damage ');
	$PHP_OUTPUT.=('while the planet did '.$planet_damage.' damage<br /><br />');

}

?>