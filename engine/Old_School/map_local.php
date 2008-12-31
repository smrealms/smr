<?
//BROKEN
//// check if we have a course plotted
//$db->query('
//SELECT course FROM player_plotted_course 
//WHERE account_id=' . $player->getAccountID() . '
//AND game_id=' . $player->getGameID() . '
//LIMIT 1'
//);
//
//if ($db->next_record()) {
//	// get the array back
//	$plot_sectors = unserialize($db->f('course'));
//}
//
//$db->query('SELECT
//sector.galaxy_id as galaxy_id,
//galaxy.galaxy_name as galaxy_name
//FROM sector,galaxy
//WHERE sector.sector_id=' . $player->getSectorID() . '
//AND game_id=' . SmrSession::$game_id . '
//AND galaxy.galaxy_id = sector.galaxy_id
//LIMIT 1');
//
//$db->next_record();
//
//$galaxy_name = $db->f('galaxy_name');
//$galaxy_id = $db->f('galaxy_id');
//
//// get our rank
//$rank_id = $account->get_rank();
//
//$db->query('
//SELECT
//MIN(sector_id),
//COUNT(*)
//FROM sector
//WHERE galaxy_id=' . $galaxy_id . '
//AND game_id=' . SmrSession::$game_id);
//
//$db->next_record();
//
//$width = $height = sqrt($db->f('COUNT(*)'));
//$start = $db->f('MIN(sector_id)');
//$current_y = floor(($player->getSectorID() - $start)/$width);
//$current_x = ($player->getSectorID() - $start) % $width;
//
//for($i=-2;$i<3;++$i) {
//	for($j=-2;$j<3;++$j) {
//		$temp_y = $current_y + $i;
//		$temp_x = $current_x + $j;
//		if($temp_y < 0) $temp_y += $height;
//		if($temp_x >= $width) $temp_x -= $width;
//		if($temp_y >= $height) $temp_y -= $height;
//		if($temp_x < 0) $temp_x += $width;
//
//		$sectors[] = ($temp_y*$width) + $temp_x + $start;
//	}
//}
//
///*
////any ticker news?
//$db->query('SELECT * FROM player_has_ticker WHERE game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID().' AND type != 'block'');
//if ($db->next_record()) {
//	$PHP_OUTPUT.=('<div align=center>');
//	echo_table();
//	$PHP_OUTPUT.=('<tr><th align=center>Time</th>');
//	if ($db->f('type') == 'news') {
//
//		$PHP_OUTPUT.=('<th align=center>News</th></tr>');
//		//get recent news (5 mins)
//		$max = time() - 5*60;
//		$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND time >= $max ORDER BY time DESC LIMIT 1');
//		if ($db->next_record()) {
//
//			$PHP_OUTPUT.=('<tr><td align=center>');
//			$time = $db->f('time');
//			$PHP_OUTPUT.=(date('n/j/Y g:i:s A', $time));
//			$PHP_OUTPUT.=('</td><td align=center>');
//			$msg = stripslashes($db->f('news_message'));
//			$PHP_OUTPUT.=('.$db->escapeString($msg');
//			$PHP_OUTPUT.=('</td></tr></table>');
//
//		} else {
//
//			$PHP_OUTPUT.=('<tr><td align=center>');
//			$time = time();
//			$PHP_OUTPUT.=(date('n/j/Y g:i:s A', $time));
//			$PHP_OUTPUT.=('</td><td align=center>');
//			$PHP_OUTPUT.=('Nothing to report!');
//			$PHP_OUTPUT.=('</td></tr></table>');
//
//		}
//
//	} elseif ($db->f('type') == 'scout') {
//
//		$PHP_OUTPUT.=('<th align=center>Message</th></tr>');
//		//get recent news (5 mins)
//		$max = time() - 5*60;
//		$tim_1 = $db->f('time');
//		if ($db->f('time') >= $max) {
//
//			$PHP_OUTPUT.=('<tr><td align=center>');
//			$time = $db->f('time');
//			$PHP_OUTPUT.=(date('n/j/Y g:i:s A', $time));
//			$PHP_OUTPUT.=('</td><td align=center>');
//			$msg = stripslashes($db->f('recent'));
//			$PHP_OUTPUT.=('.$db->escapeString($msg');
//			$PHP_OUTPUT.=('</td></tr></table>');
//
//		} else {
//
//			$PHP_OUTPUT.=('<tr><td align=center>');
//			$time = time();
//			$msg = 'Show this to Az...$time,$max,$tim_1';
//			$PHP_OUTPUT.=(date('n/j/Y g:i:s A', $time));
//			$PHP_OUTPUT.=('</td><td align=center>');
//			$PHP_OUTPUT.=('Nothing to Report');
//			$PHP_OUTPUT.=('</td></tr></table>');
//
//		}
//
//	}
//	$PHP_OUTPUT.=('</div>');
//}
//*/
//
//$sectors_in = implode(',', $sectors);
//
//$PHP_OUTPUT .= 'Local map of the known <b><big>';
//$PHP_OUTPUT .= $galaxy_name;
//$PHP_OUTPUT .= '</big></b> galaxy<br><br>';
//
//// Grab all the locations info
//$db->query('SELECT sector_id,location_type_id FROM location WHERE sector_id IN (' . $sectors_in . ')  AND game_id=' . SmrSession::$game_id);
//
//while($db->next_record()) {
//	$locations[$db->f('sector_id')][] = $db->f('location_type_id');
//	$temp[] = $db->f('location_type_id');
//}
//
//// Cache locations for later
//if(isset($locations)) {
//
//	$db->query('SELECT location_type_id,location_name,location_processor,location_image FROM location_type WHERE location_type_id IN (' . implode(',', $temp) . ') LIMIT ' . count($temp));
//
//	while($db->next_record()) {
//		$location_cache[$db->f('location_type_id')] = array(stripslashes($db->f('location_name')),$db->f('location_processor'),$db->f('location_image'));
//	}
//	
//	unset($temp);
//}
//
//// Grab any planets
//$db->query('SELECT sector_id FROM planet WHERE sector_id IN (' . $sectors_in . ')  AND game_id=' . SmrSession::$game_id);
//
//// Planets (This must go AFTER the locations stuff);
//while($db->next_record()) {
//	// We actually treat planets as a special type of location
//	$locations[$db->f('sector_id')][] = 0;
//}
//
//// We add an entry into the location cache for it
//$location_cache[0] = array('Planet','planet_examine.php','images/planet.gif');
//
//// Cache good names for later
//$db->query('SELECT good_id,good_name FROM good');
//while($db->next_record()) {
//	$goods[$db->f('good_id')] = $db->f('good_name');
//}
//
//// Grab all the port info in one go
//$db->query('SELECT sector_id,port_info FROM player_visited_port WHERE sector_id IN (' . $sectors_in . ') AND account_id=' . SmrSession::$account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 25');
//while($db->next_record()) {
//	$ports[$db->f('sector_id')] = $db->f('port_info');
//
//}
//
//// Sector links
//$db->query('SELECT sector_id,link_up,link_right,link_down,link_left FROM sector WHERE sector_id IN (' . $sectors_in . ')  AND game_id=' . SmrSession::$game_id . ' LIMIT 25');
//
//while($db->next_record()) {
//	$sector_cache[$db->f('sector_id')] = array($db->f('link_up'),$db->f('link_right'),$db->f('link_down'),$db->f('link_left'));
//}
//
//// Scan the adjacent sectors if required
//if($ship->hasScanner()) {
//	// Forces in adjacent sector 
//	foreach ($sector_cache[$player->getSectorID()] as $adjacent_sector) {
//		if($adjacent_sector) {
//			$adjacent[] = $adjacent_sector;
//		}
//	}
//}
//
//$adjacent[] = $player->getSectorID();
//$adjacent_in = implode(',', $adjacent);
//
//$query = '
//SELECT sector_has_forces.sector_id AS sector_id,COUNT(*) 
//FROM sector_has_forces';
//
//// Vets don't see newbies in racials and vice versa
//if ($galaxy_id < 9) {
//	$query .= ',account_has_stats,account
//				WHERE account.account_id = sector_has_forces.owner_id
//				AND account_has_stats.account_id = sector_has_forces.owner_id';
//
//	if($account->get_rank() > BEGINNER || $account->veteran == 'TRUE') {
//		$query2 = ' AND (
//		(account_has_stats.kills >= 15 OR account_has_stats.experience_traded >= 60000) OR 
//		(account_has_stats.kills >= 10 AND account_has_stats.experience_traded >= 40000)
//		OR account.veteran=\'TRUE\')';
//	}
//	else {
//		$query2 = ' AND (
//		(account_has_stats.kills < 15 AND account_has_stats.experience_traded < 60000) OR 
//		(account_has_stats.kills < 10 AND account_has_stats.experience_traded < 40000)
//		) AND account.veteran=\'FALSE\'';
//	}
//	$query2 .= ' AND ';
//}
//else {
//	$query2 = ' WHERE ';
//}
//	
//$query .= $query2 . '
//sector_has_forces.sector_id IN (' . $adjacent_in . ')
//AND sector_has_forces.game_id=' . SmrSession::$game_id . '
//GROUP BY sector_has_forces.sector_id LIMIT ' . count($adjacent);
//$db->query($query);
//
//
//while($db->next_record()) {
//	$forces[$db->f('sector_id')] = TRUE;
//}
//
//// Players in adjacent sector (UGLY! Let's store rank in the db in SMR2)
//$query = '
//SELECT player.sector_id,COUNT(*) 
//FROM player';
//	if ($galaxy_id < 9) {
//	$query .= ',account_has_stats,account	
//			WHERE account.account_id = player.account_id
//			AND account_has_stats.account_id = player.account_id';
//}
//
//$query .= $query2 . 'player.sector_id IN (' . $adjacent_in . ')
//	AND player.account_id!=' . SmrSession::$account_id . ' 
//	AND player.game_id=' . SmrSession::$game_id . ' 
//	AND player.land_on_planet=\'FALSE\' 
//	AND player.account_id NOT IN (' . implode(',', $HIDDEN_PLAYERS) . ')
//	AND player.last_cpl_action>' .  (time() - 259200) . '
//	GROUP BY player.sector_id  LIMIT ' . count($adjacent);
//
//$db->query($query);
//
//while($db->next_record()) {
//	$players[$db->f('sector_id')] = TRUE;
//}
//
//// Warps
//$db->query('SELECT sector_id_1,sector_id_2 FROM warp WHERE (sector_id_1 IN (' . $sectors_in . ') OR sector_id_2 IN (' . $sectors_in . ')) AND game_id=' . SmrSession::$game_id);
//
//while($db->next_record()) {
//	if(isset($sector_cache[$db->f('sector_id_1')])) {
//		$warps[$db->f('sector_id_1')] = $db->f('sector_id_2');
//	}
//	else {
//		$warps[$db->f('sector_id_2')] = $db->f('sector_id_1');
//	}
//}
//
//// Visited
//$db->query('SELECT sector_id FROM player_visited_sector WHERE sector_id IN (' . $sectors_in . ') AND account_id=' . SmrSession::$account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 25');
//
//while($db->next_record()) {
//	$sector_visited[$db->f('sector_id')] = TRUE;
//}
//
//$PHP_OUTPUT .= '<div align="center"><div class="lm"">';
//
//$col = 0;
//$row = 0;
//
//$num_sectors = count($sectors);
//
//$container1 = array();
//$container1['url'] = 'sector_move_processing.php';
//$container1['target_page'] = 'map_local.php';
//
//$container2 = array();
//$container2['url'] = 'skeleton.php';
//
//foreach ($sectors as $sector_id) {
//	if($col == 5) {
//		$col = 0;
//		++$row;
//	}
//
//	$PHP_OUTPUT .= '<div style="top:';
//	$PHP_OUTPUT .= (122 * $row);
//	$PHP_OUTPUT .= 'px;left:';
//	$PHP_OUTPUT .= (122 * $col);
//	$PHP_OUTPUT .= 'px;"';
//
//	// color the sector
//	if ($player->getSectorID() == $sector_cache[$sector_id][0] ||
//		$player->getSectorID() == $sector_cache[$sector_id][2] ||
//		$player->getSectorID() == $sector_cache[$sector_id][3] ||
//		$player->getSectorID() == $sector_cache[$sector_id][1]
//	) {
//		
//		$PHP_OUTPUT .= 'class="lmsa">';
//		
//		$container1['target_sector'] = $sector_id;
//		$PHP_OUTPUT .= '<a onfocus="blur()" class="lmsa';
//		if(isset($sector_visited[$sector_id])) {
//			$PHP_OUTPUT .= ' yellow';
//		}
//		else {
//			$PHP_OUTPUT .= ' green';
//		}
//		$PHP_OUTPUT .= '" href="';
//		$PHP_OUTPUT .= 'loader.php?sn=';
//		$PHP_OUTPUT .= SmrSession::get_new_sn($container1);
//		$PHP_OUTPUT .= '">#';
//		$PHP_OUTPUT .= $sector_id;
//		$PHP_OUTPUT .= '</a>';
//	}
//	else if ($player->getSectorID() == $sector_id) {
//		$PHP_OUTPUT .= 'class="lmsc">';
//		$container=array();
//		$container['url'] = 'skeleton.php';
//		$container['body'] = 'current_sector.php';
//		$PHP_OUTPUT .= '<a onfocus="blur()" class="lmsc" href="';
//		$PHP_OUTPUT .= 'loader.php?sn=';
//		$PHP_OUTPUT .= SmrSession::get_new_sn($container);
//		$PHP_OUTPUT .= '">#';
//		$PHP_OUTPUT .= $sector_id;
//		$PHP_OUTPUT .= '</a>';
//	}
//	elseif (!isset($sector_visited[$sector_id])) {
//		$PHP_OUTPUT .= 'class="lmsv">#';
//		$PHP_OUTPUT .= $sector_id;
//	}
//	else {
//		$PHP_OUTPUT .= 'class="lms">#';
//		$PHP_OUTPUT .= $sector_id;
//	}
//	
//	// Forces
//	if(isset($players[$sector_id]) || isset($forces[$sector_id])) {
//		$PHP_OUTPUT .= '<div class="lmpf">';
//		if(isset($players[$sector_id])) {
//			$PHP_OUTPUT .= '<img src="images/trader.jpg" alt="Trader" title="Trader">';
//		}
//		if(isset($forces[$sector_id])) {
//			$PHP_OUTPUT .= '<img src="images/forces.jpg" alt="Forces" title="Forces">';
//		}
//		$PHP_OUTPUT .= '</div>';
//	}
//
//	$PHP_OUTPUT .= '<div class="lmpw">';
//
//	// Plotted course
//	if(isset($plot_sectors) && (in_array($sector_id,$plot_sectors) || $sector_id==$player->getSectorID()) ) {
//		$PHP_OUTPUT .= '<img src="images/plot_icon.gif" title="In plotted course" alt="In plotted course">';
//	}
//			
//	// Warps
//	if(isset($warps[$sector_id]) && !isset($sector_visited[$sector_id])) {
//		if($sector_id == $player->getSectorID()) {
//			$container1['target_sector'] = $warps[$sector_id];
//			$PHP_OUTPUT .= '<a href="';
//			$PHP_OUTPUT .= 'loader.php?sn=';
//			$PHP_OUTPUT .= SmrSession::get_new_sn($container1);
//			$PHP_OUTPUT .= '">';
//		}
//		$PHP_OUTPUT .= '<img src="images/warp.gif" alt="Warp to #';
//		$PHP_OUTPUT .= $warps[$sector_id];
//		$PHP_OUTPUT .= '" title="Warp to #';
//		$PHP_OUTPUT .= $warps[$sector_id];
//		$PHP_OUTPUT .= '">';
//		if($sector_id == $player->getSectorID()) {
//			$PHP_OUTPUT .= '</a>';
//		}
//	}
//		
//	$PHP_OUTPUT .= '</div>';
//			
//	// We can skip the rest of the loop if it is unexplored
//	if(isset($sector_visited[$sector_id])) {
//		$PHP_OUTPUT .= '</div>';
//		++$col;
//		continue;
//	}
//
//	// exits
//	if($sector_cache[$sector_id][0]) {
//		$PHP_OUTPUT .= '<img class="lmlt" src="images/link_hor.gif" alt="" title="">';
//	}
//	if($sector_cache[$sector_id][2]) {
//		$PHP_OUTPUT .= '<img class="lmlb" src="images/link_hor.gif"alt="" title="">';
//	}
//	if($sector_cache[$sector_id][3]) {
//		$PHP_OUTPUT .= '<img class="lmll" src="images/link_ver.gif"alt="" title="">';
//	}
//	if($sector_cache[$sector_id][1]) {
//		$PHP_OUTPUT .= '<img class="lmlr" src="images/link_ver.gif"alt="" title="">';
//	}
//
//	// Port
//	if(isset($ports[$sector_id]) && !isset($sector_visited[$sector_id])) {
//		$PHP_OUTPUT .= '<div class="lmp">';
//		$port_goods = unserialize($ports[$sector_id ]);
//		$num_goods = count($port_goods);
//		$goods_bought = array();
//		$goods_sold = array();
//		foreach($port_goods as $good_id => $transaction) {
//			if($good_id != 'race_id'){
//				if($transaction == 'Buy') {
//					$goods_bought[] = $good_id;
//				}
//				else {
//					$goods_sold[] = $good_id;
//				}
//			}
//		}
//		if($sector_id == $player->getSectorID()) {
//			$container3 = array();
//			$container3['url'] = 'skeleton.php';
//			$container3['body'] = 'shop_goods.php';
//			$PHP_OUTPUT .= '<a href="';
//			$PHP_OUTPUT .= 'loader.php?sn=';
//			$PHP_OUTPUT .= SmrSession::get_new_sn($container3);
//			$PHP_OUTPUT .= '">';
//		}
//		$PHP_OUTPUT .= '<img src="images/buy.gif" alt="Goods Sold" title="Goods Sold">';
//		sort($goods_sold);
//		foreach($goods_sold as $good_id) {
//			if ($player->getAlignment() > -100 && ($good_id == 5 || $good_id == 9 || $good_id == 12)) continue;
//			$PHP_OUTPUT .= '<img src="images/port/';
//			$PHP_OUTPUT .= $good_id;
//			$PHP_OUTPUT .= '.gif" alt="';
//			$PHP_OUTPUT .= $goods[$good_id];
//			$PHP_OUTPUT .= '" title="';
//			$PHP_OUTPUT .= $goods[$good_id];
//			$PHP_OUTPUT .= '">';
//		}
//		$PHP_OUTPUT .= '<br/><img src="images/sell.gif" alt="Goods Bought" title="Goods Bought">';
//		sort($goods_bought);
//		foreach($goods_bought as $good_id) {
//			if ($player->getAlignment() > -100 && ($good_id == 5 || $good_id == 9 || $good_id == 12)) continue;
//			$PHP_OUTPUT .= '<img src="images/port/';
//			$PHP_OUTPUT .= $good_id;
//			$PHP_OUTPUT .= '.gif" alt="';
//			$PHP_OUTPUT .= $goods[$good_id];
//			$PHP_OUTPUT .= '" title="';
//			$PHP_OUTPUT .= $goods[$good_id];
//			$PHP_OUTPUT .= '">';
//		}
//		if($sector_id == $player->getSectorID()) {
//			$PHP_OUTPUT .= '</a>';
//		}
//		$PHP_OUTPUT .= '</div>';
//	}
//
//	// Locations
//	if(isset($locations[$sector_id]) && !isset($sector_visited[$sector_id])) {
//		sort($locations[$sector_id]);
//		$PHP_OUTPUT .= '<div class="lml">';
//		foreach($locations[$sector_id] as $location) {
//			if($sector_id == $player->getSectorID() && $location_cache[$location][1]) {
//				$container2['body'] = $location_cache[$location][1];
//				$PHP_OUTPUT .= '<a href="';
//				$PHP_OUTPUT .= 'loader.php?sn=';
//				$PHP_OUTPUT .= SmrSession::get_new_sn($container2);
//				$PHP_OUTPUT .= '">';
//			}
//			$PHP_OUTPUT .= '<img src="';
//			$PHP_OUTPUT .= $location_cache[$location][2];
//			$PHP_OUTPUT .= '"alt="';
//			$PHP_OUTPUT .= $location_cache[$location][0];
//			$PHP_OUTPUT .= '" title="';
//			$PHP_OUTPUT .= $location_cache[$location][0];
//		  	$PHP_OUTPUT .= '">';
//			if($sector_id == $player->getSectorID() && $location_cache[$location][1]) {
//				$PHP_OUTPUT .= '</a>';
//			}
//
//		}
//		$PHP_OUTPUT .= '</div>';
//	}
//
//	$PHP_OUTPUT .= '</div>';
//	++$col;
//}
//
//// Tidy up
//unset($warps,$players,$sectors,$locations,$goods,$location_cache,$sector_cache,$sector_visited,$adjacent,$sectors_in);
//
//$PHP_OUTPUT .= '</div></div>';




////////////////////////////////////////////////////////////
//
//	Script:		map_local.php
//	Purpose:	Displays Local Map
//
////////////////////////////////////////////////////////////



$db->query('SELECT
sector.galaxy_id as galaxy_id,
galaxy.galaxy_name as galaxy_name
FROM sector,galaxy
WHERE sector.sector_id=' . $player->getSectorID() . '
AND game_id=' . SmrSession::$game_id . '
AND galaxy.galaxy_id = sector.galaxy_id
LIMIT 1');
$db->next_record();

$galaxy_name = $db->f('galaxy_name');
$galaxy_id = $db->f('galaxy_id');

$smarty->assign('GalaxyName',$galaxy_name);

$smarty->assign('HeaderTemplateInclude','includes/LocalMapJS.inc');

$db->query('
SELECT
MIN(sector_id),
COUNT(*)
FROM sector
WHERE galaxy_id=' . $galaxy_id . '
AND game_id=' . SmrSession::$game_id);

$db->next_record();

global $col,$rows,$size,$offset;
$size = $db->f('COUNT(*)');
$col = $rows = sqrt($size);
//echo $db->f('COUNT(*)');
$offset = $top_left = $db->f('MIN(sector_id)');
//$current_y = floor(($player->getSectorID() - $start)/$width);
//$current_x = ($player->getSectorID() - $start) % $width;

$zoomOn = false;
if(isset($var['Dir']))
{
	$zoomOn = true;
	if ($var['Dir'] == 'Up')
	{
		$player->decreaseZoom(1);
	}
	elseif ($var['Dir'] == 'Down')
	{
		$player->increaseZoom(1);
	}
}
$dist = $player->getZoom();

$smarty->assign('isZoomOn',$zoomOn);

$container = array();
$container['url'] = 'skeleton.php';
$container['Dir'] = 'Down';
$container['rid'] = 'zoom_down';
$container['body'] = 'map_local.php';
$container['valid_for'] = -5;
$smarty->assign('ZoomDownLink',SmrSession::get_new_href($container));
$container['Dir'] = 'Up';
$container['rid'] = 'zoom_up';
$smarty->assign('ZoomUpLink',SmrSession::get_new_href($container));

$span = 1 + ($dist * 2);
//echo $player->getZoom();

$upLeft = $dist;

//figure out what should be the top left and bottom right
//$col = $GAL_NAMES[$GAL_ID]['Length'];
//$rows = $GAL_NAMES[$GAL_ID]['Height'];
//$size = $col * $rows;
//$sectorKeys=array_keys($SECTOR);
//$first_sec = array_shift($sectorKeys);
//$offset = $first_sec - 1;
$top_left = $player->getSectorID();
//go left then up
for ($i=1;$i<=$upLeft&&$i<=$col/2;$i++)
	$top_left = get_real_left($top_left);
for ($i=1;$i<=$upLeft&&$i<=$rows/2;$i++)
	$top_left = get_real_up($top_left);
	
// check if we have a course plotted
$db->query('SELECT course FROM player_plotted_course 
WHERE account_id=' . $player->getAccountID() . '
AND game_id=' . $player->getGameID() . '
LIMIT 1'
);
if ($db->next_record())
{
	// get the array back
	$plot_sectors = unserialize($db->f('course'));
}

//get the ports in this range
$ports = array();
$mines = array();
$leftMostSec = $top_left;

//$sql = $db->query('SELECT * FROM port WHERE sector_id IN (' . escape_string($sectors_displayed,false) . ') AND game_id = ' . $GAME_ID);
//if (get_size($sql) > 0)
//{
//	while ($result = next_record($sql))
//	{
//		if ($player->getAlignment() > -100 && in_array($result['good_id'], $EVIL_GOODS)) continue;
//		if ($player->getAlignment() < 100 && in_array($result['good_id'], $GOOD_GOODS)) continue;
//		$ports[$result['sector_id']]['Goods'][$result['type']][$result['good_id']] = $result['amount'];
//	}
//}
//$sql = $db->query('SELECT * FROM port_refresh WHERE sector_id IN (' . escape_string($sectors_displayed,false) . ') AND game_id = ' . $GAME_ID);
//while ($result = next_record($sql))
//{
//	$ports[$result['sector_id']]['Reinforce'] = $result['reinforce_time'];
//}
//$sql = $db->query('SELECT * FROM port_refresh WHERE sector_id IN (' . escape_string($sectors_displayed,false) . ') AND game_id = ' . $GAME_ID);
//while ($result = next_record($sql))
//{
//	$ports[$result['sector_id']]['Reinforce'] = $result['reinforce_time'];
//}
//$sql = $db->query('SELECT * FROM sector_has_mining WHERE sector_id IN (' . escape_string($all_secs,false) . ') AND game_id = ' . $GAME_ID);
//while ($result = next_record($sql))
//{
//	$mines[$result['sector_id']] = TRUE;
//}
//print_r_real($mines);
//get traders and forces in adjacent sector
//$adaj_sectors = array($player->getSectorID(),$SECTOR[$player->getSectorID()]['Up'],$SECTOR[$player->getSectorID()]['Down'],$SECTOR[$player->getSectorID()]['Left'],$SECTOR[$player->getSectorID()]['Right']);
//$sql = $db->query('SELECT * FROM player WHERE sector_id IN (' . escape_string($adaj_sectors,false) . ') AND game_id = ' . $GAME_ID.
//				' AND account_id != '.$ACCOUNT_ID.' AND land_on_planet = \'FALSE\'');
//if (get_size($sql) > 0)
//{
//	while ($result = next_record($sql))
//	{
//		if (has_privilege('Invisible', $result['account_id'])) continue;
//		$traders[$result['sector_id']] = TRUE;
//	}
//}
//$sql = $db->query('SELECT * FROM npcs WHERE sector_id IN (' . escape_string($adaj_sectors,false) . ') AND game_id = ' . $GAME_ID.' AND land_on_planet = \'FALSE\'');
//if (get_size($sql) > 0)
//{
//	while ($result = next_record($sql))
//		$traders[$result['sector_id']] = TRUE;
//}
//
//$sql = $db->query('SELECT * FROM sector_has_forces WHERE sector_id IN (' . escape_string($adaj_sectors,false) . ') AND game_id = ' . $GAME_ID . ' AND expire>'.$TIME);
//if (get_size($sql) > 0)
//{
//	while ($result = next_record($sql))
//		$forces[$result['sector_id']] = TRUE;
//}

//get planets
//$sql = $db->query('SELECT * FROM planet WHERE sector_id IN (' . escape_string($all_secs,false) . ') AND game_id = ' . $GAME_ID);
//if (get_size($sql) > 0)
//{
//	while ($result = next_record($sql))
//		$planets[$result['sector_id']] = TRUE;
//}

$mapSectors = array();
$leftMostSec = $top_left;
for ($i=1;$i<=$span&&$i<=$rows;$i++)
{
	$mapSectors[$i] = array();
	//new row
	if ($i!=1) $leftMostSec = get_real_down($leftMostSec);
	
	//get left most sector for this row
	$this_sec = $leftMostSec;
	//iterate through the columns
	for ($j=1;$j<=$span&&$j<=$col;$j++)
	{
		//new sector
		if ($j!=1) $this_sec = get_real_right($this_sec);
		$mapSectors[$i][$j] =& SmrSector::getSector(SmrSession::$game_id,$this_sec,SmrSession::$account_id);
	}
}
$smarty->assign_by_ref('MapSectors',$mapSectors);

function get_real_up($sector)
{
	global $offset, $size, $col, $rows;
	$sector_check = $sector - $offset;
	if ($sector_check <= $col) $up = $sector + $size - $col;
	else $up = $sector - $col;
	return $up;
}
function get_real_down($sector)
{
	global $offset, $size, $col, $rows;
	$sector_check = $sector - $offset;
	if ($sector_check >= ($size - $col + 1)) $down = $sector - $size + $col;
	else $down = $sector + $col;
	return $down;
}
function get_real_left($sector)
{
	global $offset, $size, $col, $rows;
	$sector_check = $sector - $offset;
	if (($sector_check - 1) % $col == 0) $left = $sector + $col - 1;
	else $left = $sector - 1;
	return $left;
}
function get_real_right($sector)
{
	global $offset, $size, $col, $rows;
	$sector_check = $sector - $offset;
	if ($sector_check % $col == 0) $right = $sector - $col + 1;
	else $right = $sector + 1;
	return $right;
}
?>