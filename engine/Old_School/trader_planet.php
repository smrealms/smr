<?		
require_once($LIB . 'global/smr_alliance.inc');
		require_once(get_file_loc('SmrPlanet.class.inc'));
function echo_time($sek) {

	$i = sechof('%d:%d:%d ',
				 $sek / 3600 % 24,
				 $sek / 60 % 60,
				 $sek % 60);
	return $i;
}

$smarty->assign('PageTopic','PLANETS');

include($ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_trader_menue();

$db2 = new SMR_DB();
$db->query('SELECT * FROM player, planet WHERE player.account_id = planet.owner_id AND ' .
											  'player.game_id = '.$player->getGameID().' AND ' .
											  'planet.game_id = '.$player->getGameID().' AND ' .
											  'player.account_id = '.$player->getAccountID());
$smarty->assign('PageTopic','YOUR PLANET');
if ($db->nf() > 0) {

	$PHP_OUTPUT.=('<div align="center">');
	$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="3" border="0" class="standard" width="95%">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Name</th>');
	$PHP_OUTPUT.=('<th align="center">Sector</th>');
	$PHP_OUTPUT.=('<th align="center">Galaxy</th>');
	$PHP_OUTPUT.=('<th align="center">G</th>');
	$PHP_OUTPUT.=('<th align="center">H</th>');
	$PHP_OUTPUT.=('<th align="center">T</th>');
	$PHP_OUTPUT.=('<th align="center">Build</th>');
	$PHP_OUTPUT.=('<th align="center">Shields</th>');
	$PHP_OUTPUT.=('<th align="center">Drones</th>');
	$PHP_OUTPUT.=('<th align="center">Supplies</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->next_record()) {

		$planet =& SmrPlanet::getPlanet(SmrSession::$game_id,$db->f('sector_id'));
		$planet_sector =& SmrSector::getSector(SmrSession::$game_id, $db->f('sector_id'), SmrSession::$account_id);
		$planet_sec = $db->f('sector_id');
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td>'.$planet->planet_name.'</td>');
		$PHP_OUTPUT.=('<td align="right">'.$planet->sector_id.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$planet_sector->galaxy_name.'</td>');
		$PHP_OUTPUT.=('<td align="center">' . $planet->construction[1] . '</td>');
		$PHP_OUTPUT.=('<td align="center">' . $planet->construction[2] . '</td>');
		$PHP_OUTPUT.=('<td align="center">' . $planet->construction[3] . '</td>');
		$PHP_OUTPUT.=('<td align="center">');

		if ($planet->build()) {

			$PHP_OUTPUT.=($planet->current_building_name.'<br>');
			$PHP_OUTPUT.=(echo_time($planet->time_left));

		} else
			$PHP_OUTPUT.=('Nothing');

		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td align="center">'.$planet->shields.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$planet->drones.'</td>');
		$PHP_OUTPUT.=('<td align="left">');
		foreach ($planet->stockpile as $id => $amount)

			if ($amount > 0) {

				$db2->query('SELECT * FROM good WHERE good_id = '.$id);
				if ($db2->next_record())
					$PHP_OUTPUT.=($db2->f('good_name') . ': '.$amount.'<br>');
				$supply = true;
			}

		if (!$supply)
			$PHP_OUTPUT.=('none');

	}

	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('</div>');

} else
	$PHP_OUTPUT.=('You don\'t have a planet claimed!<br><br>');
	
if ($player->getAllianceID() != 0) {
	
	$alliance = new SMR_ALLIANCE($player->getAllianceID(), SmrSession::$game_id);
	
	$smarty->assign('PageTopic','PLANET LIST FOR '.$player->getAllianceName().' ('.$player->getAllianceID().')');
	
	$db2 = new SMR_DB();
	if (!isset($planet_sec)) $planet_sec = 0;
	$db->query('SELECT * FROM player, planet WHERE player.game_id = planet.game_id AND ' .
												  'owner_id = account_id AND ' .
												  'player.game_id = '.$player->getGameID().' AND ' .
												  'planet.game_id = '.$player->getGameID().' AND ' .
												  'planet.sector_id != '.$planet_sec.' AND ' .
												  'alliance_id = '.$player->getAllianceID().' ' .
											'ORDER BY planet.sector_id');
	if ($db->nf() > 0) {
	
		$PHP_OUTPUT.=('<div align="center">');
		$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="3" border="0" class="standard" width="95%">');
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<th align="center">Name</th>');
		$PHP_OUTPUT.=('<th align="center">Owner</th>');
		$PHP_OUTPUT.=('<th align="center">Sector</th>');
		$PHP_OUTPUT.=('<th align="center">Galaxy</th>');
		$PHP_OUTPUT.=('<th align="center">G</th>');
		$PHP_OUTPUT.=('<th align="center">H</th>');
		$PHP_OUTPUT.=('<th align="center">T</th>');
		$PHP_OUTPUT.=('<th align="center">Build</th>');
		$PHP_OUTPUT.=('<th align="center">Shields</th>');
		$PHP_OUTPUT.=('<th align="center">Drones</th>');
		$PHP_OUTPUT.=('<th align="center">Supplies</th>');
		$PHP_OUTPUT.=('</tr>');
	
		while ($db->next_record()) {
	
			$planet =& SmrPlanet::getPlanet(SmrSession::$game_id,$db->f('sector_id'));
			$planet_sector =& SmrSector::getSector(SmrSession::$game_id, $db->f('sector_id'), SmrSession::$account_id);
			$planet_owner =& SmrPlayer::getPlayer($planet->owner_id, SmrSession::$game_id);
			$planet->build();
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td>'.$planet->planet_name.'</td>');
			$PHP_OUTPUT.=('<td>'.$planet_owner->player_name.'</td>');
			$PHP_OUTPUT.=('<td align="center">'.$planet->sector_id.'</td>');
			$PHP_OUTPUT.=('<td align="center">'.$planet_sector->galaxy_name.'</td>');
			$PHP_OUTPUT.=('<td align="center">' . $planet->construction[1] . '</td>');
			$PHP_OUTPUT.=('<td align="center">' . $planet->construction[2] . '</td>');
			$PHP_OUTPUT.=('<td align="center">' . $planet->construction[3] . '</td>');
			$PHP_OUTPUT.=('<td align="center">');
	
			if ($planet->build()) {
	
				$PHP_OUTPUT.=($planet->current_building_name.'<br>');
				$PHP_OUTPUT.=(echo_time($planet->time_left));
	
			} else
				$PHP_OUTPUT.=('Nothing');
	
			$PHP_OUTPUT.=('</td>');
			$PHP_OUTPUT.=('<td align="center">'.$planet->shields.'</td>');
			$PHP_OUTPUT.=('<td align="center">'.$planet->drones.'</td>');
			$PHP_OUTPUT.=('<td align="left">');
			$supply = false;
			foreach ($planet->stockpile as $id => $amount)
	
				if ($amount > 0) {
	
					$db2->query('SELECT * FROM good WHERE good_id = '.$id);
					if ($db2->next_record())
						$PHP_OUTPUT.=($db2->f('good_name') . ': '.$amount.'<br>');
					$supply = true;
				}
	
			if (!$supply)
				$PHP_OUTPUT.=('none');
			$PHP_OUTPUT.=('</td>');
		}
	
		$PHP_OUTPUT.=('</table>');
		$PHP_OUTPUT.=('</div>');
	
	} elseif ($planet_sec == 0)
		$PHP_OUTPUT.=('Your alliance has no claimed planets!');
	else
		$PHP_OUTPUT.=('Your planet is the only planet in the alliance!');

}

?>