<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
define('MINES',0);
define('CDS',1);
define('SDS',2);
$db->query('SELECT leader_id, alliance_id, alliance_name FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',stripslashes($db->getField('alliance_name')) . ' (' . $db->getField('alliance_id') . ')');
//$template->assign('PageTopic',$player->getAllianceName() . ' (' . $alliance_id . ')');
include(get_file_loc('menue.inc'));
create_alliance_menue($alliance_id,$db->getField('leader_id'));

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
$categorySQL = $category.' '.$order;

if (!isset($var['subcategory']))
	$subcategory = 'expire_time ASC';
else
	$subcategory = $var['subcategory'];
    
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
player.player_name as player_name,
sector_has_forces.sector_id AS sector_id,
sector_has_forces.game_id AS game_id,
sector_has_forces.mines as mines,
sector_has_forces.combat_drones as combat_drones,
sector_has_forces.scout_drones as scout_drones,
sector_has_forces.expire_time as expire_time
FROM sector_has_forces,player
WHERE player.game_id=' . $player->getGameID() . '
AND sector_has_forces.game_id=' . $player->getGameID() . '
AND player.alliance_id=' . $alliance_id . '
AND sector_has_forces.owner_id=player.account_id
ORDER BY ' . $categorySQL . ', ' . $subcategory);

$PHP_OUTPUT.= '<div align="center">';

if ($db->getNumRows() > 0)
{
    $PHP_OUTPUT.= 'Your alliance currently has ';
    $PHP_OUTPUT.= $db->getNumRows();
    $PHP_OUTPUT.= ' stacks of forces in the universe!<br />';
    
    $hardwareTypes =& Globals::getHardwareTypes();
    
    $PHP_OUTPUT.=create_table();
    $PHP_OUTPUT.=('<th>Number of Force</th><th>Value</th></tr>');
    $PHP_OUTPUT.=('<tr><td><span class="yellow">' . $total[MINES] . '</span> mines</td><td><span class="yellow">' . number_format($total[MINES] * $hardwareTypes[HARDWARE_MINE]['Cost']) . '</span> credits</td></tr>');
    $PHP_OUTPUT.=('<tr><td><span class="yellow">' . $total[CDS] . '</span> combat drones</td><td><span class="yellow">' . number_format($total[CDS] * $hardwareTypes[HARDWARE_COMBAT]['Cost']) . '</span> credits</td></tr>');
    $PHP_OUTPUT.=('<tr><td><span class="yellow">' . $total[SDS] . '</span> scout drones</td><td><span class="yellow">' . number_format($total[SDS] * $hardwareTypes[HARDWARE_SCOUT]['Cost']) . '</span> credits</td></tr>');
    $PHP_OUTPUT.=('<tr><td><span class="yellow bold">' . array_sum($total) . '</span> forces</td><td><span class="yellow bold">' . number_format($total[MINES] * $hardwareTypes[HARDWARE_MINE]['Cost'] + $total[CDS] * $hardwareTypes[HARDWARE_COMBAT]['Cost'] + $total[SDS] * $hardwareTypes[HARDWARE_SCOUT]['Cost']) . '</span> credits</td></tr>');
    $PHP_OUTPUT.=('</table><br />');

	$PHP_OUTPUT.= '<table class="standard inset"><tr>';

    $container = array();
    $container['url'] = 'skeleton.php';
    $container['body'] = 'alliance_forces.php';

    if ($order == 'ASC')
        $container['seq'] = 'DESC';
    else
        $container['seq'] = 'ASC';

	setCategories(&$container,'player_name',$category,$categorySQL,$subcategory);
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Player Name');
    $PHP_OUTPUT.= '</th>';
	setCategories(&$container,'sector_has_forces.sector_id',$category,$categorySQL,$subcategory);
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Sector ID');
    $PHP_OUTPUT.= '</th>';
	setCategories(&$container,'combat_drones',$category,$categorySQL,$subcategory);
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Combat Drones');
    $PHP_OUTPUT.= '</th>';
	setCategories(&$container,'scout_drones',$category,$categorySQL,$subcategory);
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Scout Drones');
    $PHP_OUTPUT.= '</th>';
	setCategories(&$container,'mines',$category,$categorySQL,$subcategory);
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Mines');
    $PHP_OUTPUT.= '</th>';
	setCategories(&$container,'expire_time',$category,$categorySQL,$subcategory);
    $PHP_OUTPUT.= '<th>';
    $PHP_OUTPUT.=create_header_link($container, 'Expire time');
    $PHP_OUTPUT.= '</th>';
    $PHP_OUTPUT.= '</tr>';

    while ($db->nextRecord())
    {
		$forceGalaxy =& SmrGalaxy::getGalaxyContaining($db->getField('game_id'),$db->getField('sector_id'));
		$PHP_OUTPUT.= '<tr><td>';
		$PHP_OUTPUT.= stripslashes($db->getField('player_name'));
		$PHP_OUTPUT.= '</td><td class="shrink noWrap">';
        $PHP_OUTPUT.= $db->getField('sector_id') . ' (' . ($forceGalaxy===null?'None':$forceGalaxy->getName());
		$PHP_OUTPUT.= ')</td><td class="shrink center">';
        $PHP_OUTPUT.= $db->getField('combat_drones');
        $PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $db->getField('scout_drones');
		$PHP_OUTPUT.= '</td><td class="shrink center">';
		$PHP_OUTPUT.= $db->getField('mines');
		$PHP_OUTPUT.= '</td><td class="shrink noWrap">';
        $PHP_OUTPUT.= date(DATE_FULL_SHORT, $db->getField('expire_time'));
        $PHP_OUTPUT.= '</td></tr>';
    }
	$PHP_OUTPUT.= '</table>';
}
else {
	$PHP_OUTPUT.= 'Your alliance has no deployed forces';
}

$PHP_OUTPUT.= '</div>';

function setCategories(&$container,$newCategory,$oldCategory,$oldCategorySQL,$subcategory)
{
    $container['category'] = $newCategory;
    if($oldCategory==$container['category'])
		$container['subcategory'] = $subcategory;
    else
		$container['subcategory'] = $oldCategorySQL;
}
?>