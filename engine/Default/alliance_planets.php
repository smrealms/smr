<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
		require_once(get_file_loc('SmrPlanet.class.inc'));
$db->query('SELECT leader_id, alliance_id, alliance_name FROM alliance WHERE game_id=' . $player->getGameID() . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',$db->getField('alliance_name') . ' (' . $db->getField('alliance_id') . ')');
//$template->assign('PageTopic',$player->getAllianceName() . ' (' . $alliance_id . ')');
include(get_file_loc('menue.inc'));
create_alliance_menue($alliance_id,$db->getField('leader_id'));

// Ugly, but funtional
$db->query('
SELECT planet.sector_id
FROM player
JOIN planet ON player.game_id = planet.game_id AND player.account_id = planet.owner_id
WHERE player.game_id=' . $player->getGameID() . '
AND player.alliance_id=' . $alliance_id . '
ORDER BY planet.sector_id
');

$PHP_OUTPUT.= '<div align="center">';

if ($db->getNumRows() > 0)
{
    $PHP_OUTPUT.= 'Your alliance currently has ';
    $PHP_OUTPUT.= $db->getNumRows();
    $PHP_OUTPUT.= ' planets in the universe!<br /><br />';
	$PHP_OUTPUT.= '<table class="standard inset"><tr><th>Name</th><th>Owner</th><th>Sector<th>G</th><th>H</th><th>T</th><th>Shields</th><th>Drones</th><th>Supplies</th><th>Build</th></tr>';

	$db2 = new SmrMySqlDatabase();

    while ($db->nextRecord())
    {
		$planet =& SmrPlanet::getPlanet(SmrSession::$game_id,$db->getField('sector_id'));
		$PHP_OUTPUT.= '<tr><td>';
		$PHP_OUTPUT.= $planet->getName();
		$PHP_OUTPUT.= '</td><td>';
		$PHP_OUTPUT.= $planet->getOwner()->getLinkedDisplayName(false);
		$PHP_OUTPUT.= '</td><td class="shrink noWrap">';
		$PHP_OUTPUT.= $planet->getSectorID();
		$PHP_OUTPUT.= '&nbsp;(';
		$PHP_OUTPUT.= $planet->getGalaxy()->getName();
		$PHP_OUTPUT.= ')</td><td class="shrink center">';
		$PHP_OUTPUT.= $planet->getBuilding(1);
		$PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $planet->getBuilding(2);
		$PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $planet->getBuilding(3);
		$PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $planet->getShields();
		$PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $planet->getCDs();
		$PHP_OUTPUT.= '</td><td class="shrink noWrap">';

		$supply = false;

		foreach ($planet->getStockpile() as $id => $amount)
		{
			if ($amount > 0)
			{
				// Get current good
				$good = Globals::getGood($id);

				$PHP_OUTPUT.= '<img src="' . $good['ImageLink'] . '" title="' . $good['Name'] . '" alt="' . $good['Name'] . '" />&nbsp;';
				$PHP_OUTPUT.= $amount;
				$PHP_OUTPUT.= '<br />';
				$supply = true;
			}
		}

		if (!$supply)
		{
			$PHP_OUTPUT.=('none');
		}

		$PHP_OUTPUT.= '</td><td class="shrink noWrap center">';
		if ($planet->hasCurrentlyBuilding())
		{
			$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
			foreach($planet->getCurrentlyBuilding() as $building)
			{
				$PHP_OUTPUT.= $PLANET_BUILDINGS[$building['ConstructionID']]['Name'];

				$PHP_OUTPUT.= '<br />'.($building['TimeRemaining'] / 3600 % 24).':'.($building['TimeRemaining'] / 60 % 60).':'.($building['TimeRemaining'] % 60).' ';
			}
		}
		else
		{
			$PHP_OUTPUT.= 'Nothing';
		}
		$PHP_OUTPUT.= '</td></tr>';
    }
	$PHP_OUTPUT.= '</table>';
}
else
{
	$PHP_OUTPUT.= 'Your alliance has no claimed planets';
}

$PHP_OUTPUT.= '</div>';
?>