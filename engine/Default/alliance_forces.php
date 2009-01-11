<?
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
define('MINES',0);
define('CDS',1);
define('SDS',2);
$db->query('SELECT leader_id, alliance_id, alliance_name FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->nextRecord();
$smarty->assign('PageTopic',stripslashes($db->getField('alliance_name')) . ' (' . $db->getField('alliance_id') . ')');
//$smarty->assign('PageTopic',$player->getAllianceName() . ' (' . $alliance_id . ')');
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_alliance_menue($alliance_id,$db->getField('leader_id'));

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
WHERE player.game_id=' . $player->getGameID() . '
AND sector_has_forces.game_id=' . $player->getGameID() . '
AND player.alliance_id=' . $alliance_id . '
AND sector_has_forces.owner_id=player.account_id');
if ($db->nextRecord()) {
	$total[MINES] = $db->getField('tot_mines');
	$total[CDS] = $db->getField('tot_cds');
	$total[SDS] = $db->getField('tot_sds');
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
WHERE player.game_id=' . $player->getGameID() . '
AND sector_has_forces.game_id=' . $player->getGameID() . '
AND player.alliance_id=' . $alliance_id . '
AND sector_has_forces.owner_id=player.account_id
AND sector.game_id=' . $player->getGameID() . '
AND sector.sector_id=sector_has_forces.sector_id
AND galaxy.galaxy_id=sector.galaxy_id
ORDER BY ' . $category . ' ' . $order);

$PHP_OUTPUT.= '<div align="center">';

if ($db->getNumRows() > 0) {

    $PHP_OUTPUT.= 'Your alliance currently has ';
    $PHP_OUTPUT.= $db->getNumRows();
    $PHP_OUTPUT.= ' stacks of forces in the universe!<br />';
    
    $PHP_OUTPUT.=create_table();
    $PHP_OUTPUT.=('<th>Number of Force</th><th>Value</th></tr>');
    $PHP_OUTPUT.=('<tr><td><span class="yellow">' . $total[MINES] . '</span> mines</td><td><span class="yellow">' . number_format($total[MINES] * 10000) . '</span> credits</td></tr>');
    $PHP_OUTPUT.=('<tr><td><span class="yellow">' . $total[CDS] . '</span> combat drones</td><td><span class="yellow">' . number_format($total[CDS] * 10000) . '</span> credits</td></tr>');
    $PHP_OUTPUT.=('<tr><td><span class="yellow">' . $total[SDS] . '</span> scout drones</td><td><span class="yellow">' . number_format($total[SDS] * 5000) . '</span> credits</td></tr>');
    $PHP_OUTPUT.=('<tr><td><span class="yellow bold">' . array_sum($total) . '</span> forces</td><td><span class="yellow bold">' . number_format($total[MINES] * 10000 + $total[CDS] * 10000 + $total[SDS] * 5000) . '</span> credits</td></tr>');
    $PHP_OUTPUT.=('</table><br />');

	$PHP_OUTPUT.= '<table cellspacing="0" class="standard inset"><tr>';

    $container = array();
    $container['url'] = 'skeleton.php';
    $container['body'] = 'alliance_forces.php';

    if ($order == 'ASC')
        $container['seq'] = 'DESC';
    else
        $container['seq'] = 'ASC';

    $container['category'] = 'player_name';
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Player Name');
    $PHP_OUTPUT.= '</th>';
    $container['category'] = 'sector_has_forces.sector_id';
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Sector ID');
    $PHP_OUTPUT.= '</th>';
    $container['category'] = 'combat_drones';
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Combat Drones');
    $PHP_OUTPUT.= '</th>';
    $container['category'] = 'scout_drones';
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Scout Drones');
    $PHP_OUTPUT.= '</th>';
    $container['category'] = 'mines';
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Mines');
    $PHP_OUTPUT.= '</th>';
    $container['category'] = 'expire_time';
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Expire time');
    $PHP_OUTPUT.= '</th>';
    $PHP_OUTPUT.= '</tr>';

    while ($db->nextRecord()) {
		$PHP_OUTPUT.= '<tr><td>';
		$PHP_OUTPUT.= stripslashes($db->getField('player_name'));
		$PHP_OUTPUT.= '</td><td class="shrink nowrap">';
        $PHP_OUTPUT.= $db->getField('sector') . ' (' . $db->getField('galaxy_name');
		$PHP_OUTPUT.= ')</td><td class="shrink center">';
        $PHP_OUTPUT.= $db->getField('combat_drones');
        $PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $db->getField('scout_drones');
		$PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $db->getField('mines');
		$PHP_OUTPUT.= '</td><td class="shrink nowrap">';
        $PHP_OUTPUT.= date('n/j/Y g:i:s A', $db->getField('expire_time'));
        $PHP_OUTPUT.= '</td></tr>';
    }
	$PHP_OUTPUT.= '</table>';
}
else {
	$PHP_OUTPUT.= 'Your alliance has no deployed forces';
}

$PHP_OUTPUT.= '</div>';
?>