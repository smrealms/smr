<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}

$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

//get the sequence
if (!isset($var['seq'])) {
	SmrSession::updateVar('seq', 'ASC');
}
$order = $var['seq'];

//get the ordering info
if (!isset($var['category'])) {
	SmrSession::updateVar('category', 'player_name');
}
$category = $var['category'];

$categorySQL = $category.' '.$order;

if (!isset($var['subcategory'])) {
	SmrSession::updateVar('subcategory', 'expire_time ASC');
}
$subcategory = $var['subcategory'];

$db->query('
SELECT
sum(mines) as tot_mines,
sum(combat_drones) as tot_cds,
sum(scout_drones) as tot_sds
FROM sector_has_forces JOIN player ON player.game_id=sector_has_forces.game_id AND sector_has_forces.owner_id=player.account_id
WHERE player.game_id=' . $db->escapeNumber($alliance->getGameID()) . '
AND player.alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
AND expire_time >= ' . $db->escapeNumber(TIME));

if ($db->nextRecord()) {
	$total['Mines'] = $db->getInt('tot_mines');
	$total['CDs'] = $db->getInt('tot_cds');
	$total['SDs'] = $db->getInt('tot_sds');
}

$db->query('
SELECT sector_has_forces.sector_id, sector_has_forces.owner_id
FROM player
JOIN sector_has_forces ON player.game_id = sector_has_forces.game_id AND player.account_id = sector_has_forces.owner_id
WHERE player.game_id=' . $db->escapeNumber($alliance->getGameID()) . '
AND player.alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
AND expire_time >= ' . $db->escapeNumber(TIME) . '
ORDER BY ' . $categorySQL . ', ' . $subcategory);

$PHP_OUTPUT.= '<div align="center"><a href="http://wiki.smrealms.de/index.php?title=Game_Guide:_Forces" target="_blank"><img align="right" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Forces"/></a>';

if ($db->getNumRows() > 0) {
	$PHP_OUTPUT.= 'Your alliance currently has ';
	$PHP_OUTPUT.= $db->getNumRows();
	$PHP_OUTPUT.= ' stacks of forces in the universe!<br />';

	$hardwareTypes =& Globals::getHardwareTypes();

	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<th>Number of Force</th><th>Value</th></tr>');
	$PHP_OUTPUT.=('<tr><td><span class="yellow">' . number_format($total['Mines']) . '</span> mines</td><td><span class="creds">' . number_format($total['Mines'] * $hardwareTypes[HARDWARE_MINE]['Cost']) . '</span> credits</td></tr>');
	$PHP_OUTPUT.=('<tr><td><span class="yellow">' . number_format($total['CDs']) . '</span> combat drones</td><td><span class="creds">' . number_format($total['CDs'] * $hardwareTypes[HARDWARE_COMBAT]['Cost']) . '</span> credits</td></tr>');
	$PHP_OUTPUT.=('<tr><td><span class="yellow">' . number_format($total['SDs']) . '</span> scout drones</td><td><span class="creds">' . number_format($total['SDs'] * $hardwareTypes[HARDWARE_SCOUT]['Cost']) . '</span> credits</td></tr>');
	$PHP_OUTPUT.=('<tr><td><span class="yellow bold">' . number_format(array_sum($total)) . '</span> forces</td><td><span class="creds bold">' . number_format($total['Mines'] * $hardwareTypes[HARDWARE_MINE]['Cost'] + $total['CDs'] * $hardwareTypes[HARDWARE_COMBAT]['Cost'] + $total['SDs'] * $hardwareTypes[HARDWARE_SCOUT]['Cost']) . '</span> credits</td></tr>');
	$PHP_OUTPUT.=('</table><br />');

	$PHP_OUTPUT.= '<table class="standard inset"><tr>';

	$container = create_container('skeleton.php','alliance_forces.php');

	$container['seq'] = $order == 'ASC' ? 'DESC' : 'ASC';

	setCategories($container,'player_name',$category,$categorySQL,$subcategory);
	$PHP_OUTPUT.= '<th>';
	$PHP_OUTPUT.=create_header_link($container, 'Player Name');
	$PHP_OUTPUT.= '</th>';
	setCategories($container,'sector_has_forces.sector_id',$category,$categorySQL,$subcategory);
	$PHP_OUTPUT.= '<th>';
	$PHP_OUTPUT.=create_header_link($container, 'Sector ID');
	$PHP_OUTPUT.= '</th>';
	setCategories($container,'combat_drones',$category,$categorySQL,$subcategory);
	$PHP_OUTPUT.= '<th>';
	$PHP_OUTPUT.=create_header_link($container, 'Combat Drones');
	$PHP_OUTPUT.= '</th>';
	setCategories($container,'scout_drones',$category,$categorySQL,$subcategory);
	$PHP_OUTPUT.= '<th>';
	$PHP_OUTPUT.=create_header_link($container, 'Scout Drones');
	$PHP_OUTPUT.= '</th>';
	setCategories($container,'mines',$category,$categorySQL,$subcategory);
	$PHP_OUTPUT.= '<th>';
	$PHP_OUTPUT.=create_header_link($container, 'Mines');
	$PHP_OUTPUT.= '</th>';
	setCategories($container,'expire_time',$category,$categorySQL,$subcategory);
	$PHP_OUTPUT.= '<th>';
	$PHP_OUTPUT.=create_header_link($container, 'Expire time');
	$PHP_OUTPUT.= '</th>';
	$PHP_OUTPUT.= '</tr>';

	while ($db->nextRecord()) {
		$forces =& SmrForce::getForce($player->getGameID(), $db->getField('sector_id'), $db->getField('owner_id'));

		$PHP_OUTPUT .= '<tr>';
		$PHP_OUTPUT .= '<td>'.$forces->getOwner()->getLinkedDisplayName(false) . '</td>';
		$PHP_OUTPUT .= '<td class="shrink noWrap">' . $forces->getSectorID() . ' (' . $forces->getGalaxy()->getName() . ')</td>';
		$PHP_OUTPUT .= '<td class="shrink center">' . $forces->getCDs() . '</td>';
		$PHP_OUTPUT .= '<td class="shrink center">' . $forces->getSDs() . '</td>';
		$PHP_OUTPUT .= '<td class="shrink center">' . $forces->getMines() . '</td>';
		$PHP_OUTPUT .= '<td class="shrink noWrap">' . date(DATE_FULL_SHORT, $forces->getExpire()) . '</td>';
		$PHP_OUTPUT .= '</tr>';
	}
	$PHP_OUTPUT.= '</table>';
}
else {
	$PHP_OUTPUT.= 'Your alliance has no deployed forces';
}

$PHP_OUTPUT.= '</div>';

function setCategories(&$container,$newCategory,$oldCategory,$oldCategorySQL,$subcategory) {
	$container['category'] = $newCategory;
	if($oldCategory==$container['category']) {
		$container['subcategory'] = $subcategory;
	}
	else {
		$container['subcategory'] = $oldCategorySQL;
	}
}
?>