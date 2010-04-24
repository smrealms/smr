<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
define('MINES',0);
define('CDS',1);
define('SDS',2);
$db->query('SELECT leader_id, alliance_id, alliance_name FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
print_topic(stripslashes($db->f("alliance_name")) . ' (' . $db->f("alliance_id") . ')');
//print_topic($player->alliance_name . ' (' . $alliance_id . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($alliance_id,$db->f('leader_id'));

//get the sequence
if (!isset($var['seq']))
    $order = 'ASC';
else
    $order = $var['seq'];

//get the ordering info
if (!isset($var['category']))
    $category = 'player_name';
else
    $category = $var['category'];
    
$db->query('
SELECT 
sum(mines) as tot_mines,
sum(combat_drones) as tot_cds,
sum(scout_drones) as tot_sds
FROM sector_has_forces,player
WHERE player.game_id=' . $player->game_id . '
AND sector_has_forces.game_id=' . $player->game_id . '
AND player.alliance_id=' . $alliance_id . '
AND sector_has_forces.owner_id=player.account_id');
if ($db->next_record()) {
	$total[MINES] = $db->f("tot_mines");
	$total[CDS] = $db->f("tot_cds");
	$total[SDS] = $db->f("tot_sds");
}

// Ugly, but funtional
$db->query('
SELECT 
galaxy.galaxy_name as galaxy_name,
player.player_name as player_name,
sector_has_forces.sector_id AS sector,
sector_has_forces.mines as mines,
sector_has_forces.combat_drones as combat_drones,
sector_has_forces.scout_drones as scout_drones,
sector_has_forces.expire_time as expire_time
FROM sector_has_forces,player,sector,galaxy
WHERE player.game_id=' . $player->game_id . '
AND sector_has_forces.game_id=' . $player->game_id . '
AND player.alliance_id=' . $alliance_id . '
AND sector_has_forces.owner_id=player.account_id
AND sector.game_id=' . $player->game_id . '
AND sector.sector_id=sector_has_forces.sector_id
AND galaxy.galaxy_id=sector.galaxy_id
ORDER BY ' . $category . ' ' . $order);

echo '<div align="center">';

if ($db->nf() > 0) {

    echo 'Your alliance currently has ';
    echo $db->nf();
    echo ' stacks of forces in the universe!<br />';
    
    print_table();
    print("<th>Number of Force</th><th>Value</th></tr>");
    print("<tr><td><span class=\"yellow\">" . $total[MINES] . "</span> mines</td><td><span class=\"yellow\">" . number_format($total[MINES] * 10000) . "</span> credits</td></tr>");
    print("<tr><td><span class=\"yellow\">" . $total[CDS] . "</span> combat drones</td><td><span class=\"yellow\">" . number_format($total[CDS] * 10000) . "</span> credits</td></tr>");
    print("<tr><td><span class=\"yellow\">" . $total[SDS] . "</span> scout drones</td><td><span class=\"yellow\">" . number_format($total[SDS] * 5000) . "</span> credits</td></tr>");
    print("<tr><td><span class=\"yellow bold\">" . array_sum($total) . "</span> forces</td><td><span class=\"yellow bold\">" . number_format($total[MINES] * 10000 + $total[CDS] * 10000 + $total[SDS] * 5000) . "</span> credits</td></tr>");
    print("</table><br />");

	echo '<table cellspacing="0" class="standard inset"><tr>';

    $container = array();
    $container['url'] = 'skeleton.php';
    $container['body'] = 'alliance_forces.php';

    if ($order == 'ASC')
        $container['seq'] = 'DESC';
    else
        $container['seq'] = 'ASC';

    $container['category'] = 'player_name';
    echo '<th>';
    print_header_link($container, 'Player Name');
    echo '</th>';
    $container['category'] = 'sector_has_forces.sector_id';
    echo '<th>';
    print_header_link($container, 'Sector ID');
    echo '</th>';
    $container['category'] = 'combat_drones';
    echo '<th>';
    print_header_link($container, 'Combat Drones');
    echo '</th>';
    $container['category'] = 'scout_drones';
    echo '<th>';
    print_header_link($container, 'Scout Drones');
    echo '</th>';
    $container['category'] = 'mines';
    echo '<th>';
    print_header_link($container, 'Mines');
    echo '</th>';
    $container['category'] = 'expire_time';
    echo '<th>';
    print_header_link($container, 'Expire time');
    echo '</th>';
    echo '</tr>';

    while ($db->next_record()) {
		echo '<tr><td>';
		echo stripslashes($db->f('player_name'));
		echo '</td><td class="shrink nowrap">';
        echo $db->f('sector') . ' (' . $db->f('galaxy_name');
		echo ')</td><td class="shrink center">';
        echo $db->f('combat_drones');
        echo '</td><td class="shrink center">';
		echo $db->f('scout_drones');
		echo '</td><td class="shrink center">';
		echo $db->f('mines');
		echo '</td><td class="shrink nowrap">';
        echo date('n/j/Y g:i:s A', $db->f('expire_time'));
        echo '</td></tr>';
    }
	echo '</table>';
}
else {
	echo 'Your alliance has no deployed forces';
}

echo '</div>';
?>