<?
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
		require_once(get_file_loc('SmrPlanet.class.inc'));
$db->query('SELECT leader_id, alliance_id, alliance_name FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
$smarty->assign('PageTopic',stripslashes($db->f('alliance_name')) . ' (' . $db->f('alliance_id') . ')');
//$smarty->assign('PageTopic',$player->getAllianceName() . ' (' . $alliance_id . ')');
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_alliance_menue($alliance_id,$db->f('leader_id'));

// Ugly, but funtional
$db->query('
SELECT
planet.sector_id as sector_id,
galaxy.galaxy_name as galaxy_name,
player.player_name as player_name
FROM player,planet,sector,galaxy
WHERE player.game_id=planet.game_id
AND planet.owner_id=player.account_id
AND player.game_id=' . $player->getGameID() . '
AND planet.game_id=' . $player->getGameID() . '
AND player.alliance_id=' . $alliance_id . '
AND sector.game_id=' . $player->getGameID() . '
AND sector.sector_id=planet.sector_id
AND galaxy.galaxy_id=sector.galaxy_id
ORDER BY planet.sector_id
');

$PHP_OUTPUT.= '<div align="center">';

if ($db->nf() > 0) {

    $PHP_OUTPUT.= 'Your alliance currently has ';
    $PHP_OUTPUT.= $db->nf();
    $PHP_OUTPUT.= ' planets in the universe!<br /><br />';
	$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" class="standard inset"><tr><th>Name</th><th>Owner</th><th>Sector<th>G</th><th>H</th><th>T</th><th>Shields</th><th>Drones</th><th>Supplies</th><th>Build</th></tr>';

	$db2 = new SmrMySqlDatabase();

	// Cache the good names
	$goods_cache = array();
	$db2->query('SELECT good_id,good_name FROM good');
	while($db2->next_record()) {
		$goods_cache[$db2->f('good_id')] = $db2->f('good_name');
		if($db2->f('good_name') == 'Precious Metals') {
			$goods_cache[$db2->f('good_id')] = 'PM';
		}
	}

    while ($db->next_record()) {
		$planet =& SmrPlanet::getPlanet(SmrSession::$game_id,$db->f('sector_id'));
		$PHP_OUTPUT.= '<tr><td>';
		$PHP_OUTPUT.= $planet->planet_name;
		$PHP_OUTPUT.= '</td><td>';
		$PHP_OUTPUT.= stripslashes($db->f('player_name'));
		$PHP_OUTPUT.= '</td><td class="shrink nowrap">';
		$PHP_OUTPUT.= $planet->getSectorID();
		$PHP_OUTPUT.= '&nbsp;(';
		$PHP_OUTPUT.= $db->f('galaxy_name');
		$PHP_OUTPUT.= ')</td><td class="shrink center">';
		$PHP_OUTPUT.= $planet->getBuilding(1);
		$PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $planet->getBuilding(2);
		$PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $planet->getBuilding(3);
		$PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $planet->shields;
		$PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $planet->drones;
		$PHP_OUTPUT.= '</td><td class="shrink nowrap">';

		$supply = false;

		foreach ($planet->getStockpile() as $id => $amount) {
			if ($amount > 0) {
				$PHP_OUTPUT.= '<span class="nowrap">' . $goods_cache[$id] . '</span>: ';
				$PHP_OUTPUT.= $amount;
				$PHP_OUTPUT.= '<br />';
				$supply = true;
			}
		}

		if (!$supply) {
			$PHP_OUTPUT.=('none');
		}

		$PHP_OUTPUT.= '</td><td class="shrink nowrap center">';
		if ($planet->hasCurrentlyBuilding())
		{
			$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
			foreach($planet->getCurrentlyBuilding() as $building)
			{
				$PHP_OUTPUT.= $PLANET_BUILDINGS[$building['BuildingID']]['Name'];
				
				$PHP_OUTPUT.= '<br />'.($building['TimeRemaining'] / 3600 % 24).':'.($building['TimeRemaining'] / 60 % 60).':'.($building['TimeRemaining'] % 60).' ';
			}
		}
		else {
			$PHP_OUTPUT.= 'Nothing';
		}
		$PHP_OUTPUT.= '</td></tr>';
    }
	$PHP_OUTPUT.= '</table>';
}
else {
	$PHP_OUTPUT.= 'Your alliance has no claimed planets';
}

$PHP_OUTPUT.= '</div>';
?>