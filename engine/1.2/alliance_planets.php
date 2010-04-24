<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
		require_once(get_file_loc("smr_planet.inc"));
$db->query('SELECT leader_id, alliance_id, alliance_name FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
print_topic(stripslashes($db->f("alliance_name")) . ' (' . $db->f("alliance_id") . ')');
//print_topic($player->alliance_name . ' (' . $alliance_id . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($alliance_id,$db->f('leader_id'));

// Ugly, but funtional
$db->query('
SELECT
planet.sector_id as sector_id,
galaxy.galaxy_name as galaxy_name,
player.player_name as player_name
FROM player,planet,sector,galaxy
WHERE player.game_id=planet.game_id
AND planet.owner_id=player.account_id
AND player.game_id=' . $player->game_id . '
AND planet.game_id=' . $player->game_id . '
AND player.alliance_id=' . $alliance_id . '
AND sector.game_id=' . $player->game_id . '
AND sector.sector_id=planet.sector_id
AND galaxy.galaxy_id=sector.galaxy_id
ORDER BY planet.sector_id
');

echo '<div align="center">';

if ($db->nf() > 0) {

    echo 'Your alliance currently has ';
    echo $db->nf();
    echo ' planets in the universe!<br><br>';
	echo '<table cellspacing="0" cellpadding="0" class="standard inset"><tr><th>Name</th><th>Owner</th><th>Sector<th>G</th><th>H</th><th>T</th><th>Shields</th><th>Drones</th><th>Supplies</th><th>Build</th></tr>';

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
		$planet = new SMR_PLANET($db->f("sector_id"), SmrSession::$game_id);
		$planet->build();
		echo '<tr><td>';
		echo $planet->planet_name;
		echo '</td><td>';
		echo stripslashes($db->f('player_name'));
		echo '</td><td class="shrink nowrap">';
		echo $planet->sector_id;
		echo '&nbsp;(';
		echo $db->f('galaxy_name');
		echo ')</td><td class="shrink center">';
		echo $planet->construction[1];
		echo '</td><td class="shrink center">';
		echo $planet->construction[2];
		echo '</td><td class="shrink center">';
		echo $planet->construction[3];
		echo '</td><td class="shrink center">';
		echo $planet->shields;
		echo '</td><td class="shrink center">';
		echo $planet->drones;
		echo '</td><td class="shrink nowrap">';

		$supply = false;

		foreach ($planet->stockpile as $id => $amount) {
			if ($amount > 0) {
				echo '<span class="nowrap">' . $goods_cache[$id] . '</span>: ';
				echo $amount;
				echo '<br>';
				$supply = true;
			}
		}

		if (!$supply) {
			print("none");
		}

		echo '</td><td class="shrink nowrap center">';
		if ($planet->build()) {

			echo $planet->current_building_name;
			
			printf('<br>%d:%d:%d ',
				$planet->time_left / 3600 % 24,
				$planet->time_left / 60 % 60,
				$planet->time_left % 60
			);

		}
		else {
			echo 'Nothing';
		}
		echo '</td></tr>';
    }
	echo '</table>';
}
else {
	echo 'Your alliance has no claimed planets';
}

echo '</div>';
?>