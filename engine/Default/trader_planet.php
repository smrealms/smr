<?php
require_once(get_file_loc('smr_alliance.inc'));
require_once(get_file_loc('SmrPlanet.class.inc'));
function echo_time($sek)
{
	$i = ($sek / 3600 % 24).':'.($sek / 60 % 60).':'.($sek % 60);
	return $i;
}

$template->assign('PageTopic','Planets');

include(get_file_loc('menue.inc'));
create_trader_menue();

$db2 = new SmrMySqlDatabase();
$db->query('SELECT * FROM player, planet WHERE player.account_id = planet.owner_id AND ' .
											  'player.game_id = '.$player->getGameID().' AND ' .
											  'planet.game_id = '.$player->getGameID().' AND ' .
											  'player.account_id = '.$player->getAccountID());
$template->assign('PageTopic','Your Planet');
if ($db->getNumRows() > 0)
{
	$PHP_OUTPUT.=('<div align="center">');
	$PHP_OUTPUT.=('<table class="standard" width="95%">');
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

	while ($db->nextRecord())
	{
		$planet =& SmrPlanet::getPlanet(SmrSession::$game_id,$db->getField('sector_id'));
		$planet_sec = $db->getField('sector_id');
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td>'.$planet->getName().'</td>');
		$PHP_OUTPUT.=('<td align="right">' . $planet->getSectorID() . '</td>');
		$PHP_OUTPUT.=('<td align="center">' . $planet->getGalaxy()->getName().'</td>');
		$PHP_OUTPUT.=('<td align="center">' . $planet->getBuilding(PLANET_GENERATOR) . '</td>');
		$PHP_OUTPUT.=('<td align="center">' . $planet->getBuilding(PLANET_HANGAR) . '</td>');
		$PHP_OUTPUT.=('<td align="center">' . $planet->getBuilding(PLANET_TURRET) . '</td>');
		$PHP_OUTPUT.=('<td align="center">');

		if ($planet->hasCurrentlyBuilding())
		{
			$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
			foreach($planet->getCurrentlyBuilding() as $building)
			{
				$PHP_OUTPUT.=$PLANET_BUILDINGS[$building['ConstructionID']]['Name'].'<br />';
				$PHP_OUTPUT.=(echo_time($building['TimeRemaining']));
			}
		}
		else
			$PHP_OUTPUT.=('Nothing');

		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td align="center">'.$planet->getShields().'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$planet->getCDs().'</td>');
		$PHP_OUTPUT.=('<td align="left">');
		$supply = false;
		foreach ($planet->getStockpile() as $id => $amount)
			if ($amount > 0)
			{
				$db2->query('SELECT * FROM good WHERE good_id = '.$id);
				if ($db2->nextRecord())
					$PHP_OUTPUT.=($db2->getField('good_name') . ': '.$amount.'<br />');
				$supply = true;
			}

		if (!$supply)
			$PHP_OUTPUT.=('None.');

	}

	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('</div>');

}
else
	$PHP_OUTPUT.=('You don\'t have a planet claimed!<br /><br />');
	
if ($player->hasAlliance())
{
	
	$template->assign('PageTopic','Planet List For '.$player->getAllianceName().' ('.$player->getAllianceID().')');
	
	$db2 = new SmrMySqlDatabase();
	if (!isset($planet_sec)) $planet_sec = 0;
	$db->query('SELECT * FROM player, planet WHERE player.game_id = planet.game_id AND ' .
												  'owner_id = account_id AND ' .
												  'player.game_id = '.$player->getGameID().' AND ' .
												  'planet.game_id = '.$player->getGameID().' AND ' .
												  'planet.sector_id != '.$planet_sec.' AND ' .
												  'alliance_id = '.$player->getAllianceID().' ' .
											'ORDER BY planet.sector_id');
	if ($db->getNumRows() > 0)
	{
		$PHP_OUTPUT.=('<br /><div align="center">');
		$PHP_OUTPUT.=('<table class="standard" width="95%">');
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
	
		while ($db->nextRecord())
		{
			$planet =& SmrPlanet::getPlanet(SmrSession::$game_id,$db->getField('sector_id'));
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td>' . $planet->getName() . '</td>');
			$PHP_OUTPUT.=('<td>' . $planet->getOwner()->getLinkedDisplayName(false) . '</td>');
			$PHP_OUTPUT.=('<td align="center">' . $planet->getSectorID() . '</td>');
			$PHP_OUTPUT.=('<td align="center">' . $planet->getGalaxy()->getName().'</td>');
			$PHP_OUTPUT.=('<td align="center">' . $planet->getBuilding(PLANET_GENERATOR) . '</td>');
			$PHP_OUTPUT.=('<td align="center">' . $planet->getBuilding(PLANET_HANGAR) . '</td>');
			$PHP_OUTPUT.=('<td align="center">' . $planet->getBuilding(PLANET_TURRET) . '</td>');
			$PHP_OUTPUT.=('<td align="center">');
	
			if ($planet->hasCurrentlyBuilding())
			{
				$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
				foreach($planet->getCurrentlyBuilding() as $building)
				{
					$PHP_OUTPUT.=$PLANET_BUILDINGS[$building['ConstructionID']]['Name'].'<br />';
					$PHP_OUTPUT.=(echo_time($building['TimeRemaining']));
				}
			}
			else
				$PHP_OUTPUT.=('Nothing');
	
			$PHP_OUTPUT.=('</td>');
			$PHP_OUTPUT.=('<td align="center">'.$planet->getShields().'</td>');
			$PHP_OUTPUT.=('<td align="center">'.$planet->getCDs().'</td>');
			$PHP_OUTPUT.=('<td align="left">');
			$supply = false;
			foreach ($planet->getStockpile() as $id => $amount)
				if ($amount > 0)
				{
					$db2->query('SELECT * FROM good WHERE good_id = '.$id);
					if ($db2->nextRecord())
						$PHP_OUTPUT.=($db2->getField('good_name') . ': '.$amount.'<br />');
					$supply = true;
				}
	
			if (!$supply)
				$PHP_OUTPUT.=('none');
			$PHP_OUTPUT.=('</td>');
		}
	
		$PHP_OUTPUT.=('</table>');
		$PHP_OUTPUT.=('</div>');
	
	}
	elseif ($planet_sec == 0)
		$PHP_OUTPUT.=('Your alliance has no claimed planets!');
	else
		$PHP_OUTPUT.=('Your planet is the only planet in the alliance!');
}

?>