<?php

// check if we have a course plotted
$db->query('
SELECT course FROM player_plotted_course 
WHERE account_id=' . $player->account_id . '
AND game_id=' . $player->game_id . '
LIMIT 1'
);

if ($db->next_record()) {
	// get the array back
	$plot_sectors = unserialize($db->f("course"));
}

$db->query('SELECT
sector.galaxy_id as galaxy_id,
galaxy.galaxy_name as galaxy_name
FROM sector,galaxy
WHERE sector.sector_id=' . $player->sector_id . '
AND game_id=' . SmrSession::$game_id . '
AND galaxy.galaxy_id = sector.galaxy_id
LIMIT 1');

$db->next_record();

$galaxy_name = $db->f('galaxy_name');
$galaxy_id = $db->f('galaxy_id');

// get our rank
$rank_id = $account->get_rank();

$db->query('
SELECT
MIN(sector_id),
COUNT(*)
FROM sector
WHERE galaxy_id=' . $galaxy_id . '
AND game_id=' . SmrSession::$game_id);

$db->next_record();

$width = $height = sqrt($db->f('COUNT(*)'));
$start = $db->f('MIN(sector_id)');
$current_y = floor(($player->sector_id - $start)/$width);
$current_x = ($player->sector_id - $start) % $width;

for($i=-2;$i<3;++$i) {
	for($j=-2;$j<3;++$j) {
		$temp_y = $current_y + $i;
		$temp_x = $current_x + $j;
		if($temp_y < 0) $temp_y += $height;
		if($temp_x >= $width) $temp_x -= $width;
		if($temp_y >= $height) $temp_y -= $height;
		if($temp_x < 0) $temp_x += $width;

		$sectors[] = ($temp_y*$width) + $temp_x + $start;
	}
}

/*
//any ticker news?
$db->query("SELECT * FROM player_has_ticker WHERE game_id = $player->game_id AND account_id = $player->account_id AND type != 'block'");
if ($db->next_record()) {
	print("<div align=center>");
	print_table();
	print("<tr><th align=center>Time</th>");
	if ($db->f("type") == "news") {

		print("<th align=center>News</th></tr>");
		//get recent news (5 mins)
		$max = time() - 5*60;
		$db->query("SELECT * FROM news WHERE game_id = $player->game_id AND time >= $max ORDER BY time DESC LIMIT 1");
		if ($db->next_record()) {

			print("<tr><td align=center>");
			$time = $db->f("time");
			print(date("n/j/Y g:i:s A", $time));
			print("</td><td align=center>");
			$msg = stripslashes($db->f("news_message"));
			print("$msg");
			print("</td></tr></table>");

		} else {

			print("<tr><td align=center>");
			$time = time();
			print(date("n/j/Y g:i:s A", $time));
			print("</td><td align=center>");
			print("Nothing to report!");
			print("</td></tr></table>");

		}

	} elseif ($db->f("type") == "scout") {

		print("<th align=center>Message</th></tr>");
		//get recent news (5 mins)
		$max = time() - 5*60;
		$tim_1 = $db->f("time");
		if ($db->f("time") >= $max) {

			print("<tr><td align=center>");
			$time = $db->f("time");
			print(date("n/j/Y g:i:s A", $time));
			print("</td><td align=center>");
			$msg = stripslashes($db->f("recent"));
			print("$msg");
			print("</td></tr></table>");

		} else {

			print("<tr><td align=center>");
			$time = time();
			$msg = "Show this to Az...$time,$max,$tim_1";
			print(date("n/j/Y g:i:s A", $time));
			print("</td><td align=center>");
			print("Nothing to Report");
			print("</td></tr></table>");

		}

	}
	print("</div>");
}
*/

$sectors_in = implode(',', $sectors);

echo 'Local map of the known <b><big>';
echo $galaxy_name;
echo '</big></b> galaxy<br><br>';

// Grab all the locations info
$db->query('SELECT sector_id,location_type_id FROM location WHERE sector_id IN (' . $sectors_in . ')  AND game_id=' . SmrSession::$game_id);

while($db->next_record()) {
	$locations[$db->f('sector_id')][] = $db->f('location_type_id');
	$temp[] = $db->f('location_type_id');
}

// Cache locations for later
if(isset($locations)) {

	$db->query('SELECT location_type_id,location_name,location_processor,location_image FROM location_type WHERE location_type_id IN (' . implode(',', $temp) . ') LIMIT ' . count($temp));

	while($db->next_record()) {
		$location_cache[$db->f('location_type_id')] = array(stripslashes($db->f('location_name')),$db->f('location_processor'),$db->f('location_image'));
	}
	
	unset($temp);
}

// Grab any planets
$db->query('SELECT sector_id FROM planet WHERE sector_id IN (' . $sectors_in . ')  AND game_id=' . SmrSession::$game_id);

// Planets (This must go AFTER the locations stuff);
while($db->next_record()) {
	// We actually treat planets as a special type of location
	$locations[$db->f('sector_id')][] = 0;
}

// We add an entry into the location cache for it
$location_cache[0] = array('Planet','planet_examine.php','images/planet.gif');

// Cache good names for later
$db->query('SELECT good_id,good_name FROM good');
while($db->next_record()) {
	$goods[$db->f('good_id')] = $db->f('good_name');
}

// Grab all the port info in one go
$db->query('SELECT sector_id,port_info FROM player_visited_port WHERE sector_id IN (' . $sectors_in . ') AND account_id=' . SmrSession::$old_account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 25');
while($db->next_record()) {
	$ports[$db->f('sector_id')] = $db->f('port_info');

}

// Sector links
$db->query('SELECT sector_id,link_up,link_right,link_down,link_left FROM sector WHERE sector_id IN (' . $sectors_in . ')  AND game_id=' . SmrSession::$game_id . ' LIMIT 25');

while($db->next_record()) {
	$sector_cache[$db->f('sector_id')] = array($db->f('link_up'),$db->f('link_right'),$db->f('link_down'),$db->f('link_left'));
}

// Scan the adjacent sectors if required
if($ship->hardware[7]) {
	// Forces in adjacent sector 
	foreach ($sector_cache[$player->sector_id] as $adjacent_sector) {
		if($adjacent_sector) {
			$adjacent[] = $adjacent_sector;
		}
	}
}

$adjacent[] = $player->sector_id;
$adjacent_in = implode(',', $adjacent);

$query = '
SELECT sector_has_forces.sector_id AS sector_id,COUNT(*) 
FROM sector_has_forces';

// remove newbie gals
// Vets don't see newbies in racials and vice versa
//if ($galaxy_id < 9) {
//	$query .= ',account_has_stats,account
//				WHERE account.account_id = sector_has_forces.owner_id
//				AND account_has_stats.account_id = sector_has_forces.owner_id';
//
//	if($account->get_rank() > BEGINNER || $account->veteran == 'TRUE') {
//		$query2 = ' AND (
//		(account_has_stats.kills >= 15 OR account_has_stats.experience_traded >= 60000) OR 
//		(account_has_stats.kills >= 10 AND account_has_stats.experience_traded >= 40000)
//		OR account.veteran="TRUE")';
//	}
//	else {
//		$query2 = ' AND (
//		(account_has_stats.kills < 15 AND account_has_stats.experience_traded < 60000) OR 
//		(account_has_stats.kills < 10 AND account_has_stats.experience_traded < 40000)
//		) AND account.veteran="FALSE"';
//	}
//	$query2 .= ' AND ';
//}
//else {
	$query2 = ' WHERE ';
//}
	
$query .= $query2 . '
sector_has_forces.sector_id IN (' . $adjacent_in . ')
AND sector_has_forces.game_id=' . SmrSession::$game_id . '
GROUP BY sector_has_forces.sector_id LIMIT ' . count($adjacent);
$db->query($query);


while($db->next_record()) {
	$forces[$db->f('sector_id')] = TRUE;
}

// Players in adjacent sector (UGLY! Let's store rank in the db in SMR2)
$query = '
SELECT player.sector_id,COUNT(*) 
FROM player';
// remove newbie gals
//	if ($galaxy_id < 9) {
//	$query .= ',account_has_stats,account	
//			WHERE account.account_id = player.account_id
//			AND account_has_stats.account_id = player.account_id';
//}

$query .= $query2 . 'player.sector_id IN (' . $adjacent_in . ')
	AND player.account_id!=' . SmrSession::$old_account_id . ' 
	AND player.game_id=' . SmrSession::$game_id . ' 
	AND player.land_on_planet="FALSE" 
	AND player.account_id NOT IN (' . implode(',', $HIDDEN_PLAYERS) . ')
	AND player.last_active>' .  (time() - 259200) . '
	GROUP BY player.sector_id  LIMIT ' . count($adjacent);

$db->query($query);

while($db->next_record()) {
	$players[$db->f('sector_id')] = TRUE;
}

// Warps
$db->query('SELECT sector_id_1,sector_id_2 FROM warp WHERE (sector_id_1 IN (' . $sectors_in . ') OR sector_id_2 IN (' . $sectors_in . ')) AND game_id=' . SmrSession::$game_id);

while($db->next_record()) {
	if(isset($sector_cache[$db->f('sector_id_1')])) {
		$warps[$db->f('sector_id_1')] = $db->f('sector_id_2');
	}
	else {
		$warps[$db->f('sector_id_2')] = $db->f('sector_id_1');
	}
}

// Visited
$db->query('SELECT sector_id FROM player_visited_sector WHERE sector_id IN (' . $sectors_in . ') AND account_id=' . SmrSession::$old_account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 25');

while($db->next_record()) {
	$sector_visited[$db->f('sector_id')] = TRUE;
}

echo '<div align="center"><div class="lm">';

$col = 0;
$row = 0;

$num_sectors = count($sectors);

$container1 = array();
$container1['url'] = 'sector_move_processing.php';
$container1['target_page'] = 'map_local.php';

$container2 = array();
$container2['url'] = 'skeleton.php';

foreach ($sectors as $sector_id) {
	if($col == 5) {
		$col = 0;
		++$row;
	}

	echo '<div style="top:';
	echo (122 * $row);
	echo 'px;left:';
	echo (122 * $col);
	echo 'px;"';

	// color the sector
	if ($player->sector_id == $sector_cache[$sector_id][0] ||
		$player->sector_id == $sector_cache[$sector_id][2] ||
		$player->sector_id == $sector_cache[$sector_id][3] ||
		$player->sector_id == $sector_cache[$sector_id][1]
	) {
		
		echo 'class="lmsa">';
		
		$container1['target_sector'] = $sector_id;
		echo '<a onfocus="blur()" class="lmsa';
		if(isset($sector_visited[$sector_id])) {
			echo ' yellow';
		}
		else {
			echo ' green';
		}
		echo '" href="';
		echo 'loader2.php?sn=';
		echo SmrSession::get_new_sn($container1);
		echo '">#';
		echo $sector_id;
		echo '</a>';
	}
	else if ($player->sector_id == $sector_id) {
		echo 'class="lmsc">';
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		echo '<a onfocus="blur()" class="lmsc" href="';
		echo 'loader2.php?sn=';
		echo SmrSession::get_new_sn($container);
		echo '">#';
		echo $sector_id;
		echo '</a>';
	}
	elseif (!isset($sector_visited[$sector_id])) {
		echo 'class="lmsv">#';
		echo $sector_id;
	}
	else {
		echo 'class="lms">#';
		echo $sector_id;
	}
	
	// Forces
	if(isset($players[$sector_id]) || isset($forces[$sector_id])) {
		echo '<div class="lmpf">';
		if(isset($players[$sector_id])) {
			echo '<img src="images/trader.jpg" alt="Trader" title="Trader">';
		}
		if(isset($forces[$sector_id])) {
			echo '<img src="images/forces.jpg" alt="Forces" title="Forces">';
		}
		echo '</div>';
	}

	echo '<div class="lmpw">';

	// Plotted course
	if(isset($plot_sectors) && (in_array($sector_id,$plot_sectors) || $sector_id==$player->sector_id) ) {
		echo '<img src="images/plot_icon.gif" title="In plotted course" alt="In plotted course">';
	}
			
	// Warps
	if(isset($warps[$sector_id]) && !isset($sector_visited[$sector_id])) {
		if($sector_id == $player->sector_id) {
			$container1['target_sector'] = $warps[$sector_id];
			echo '<a href="';
			echo 'loader2.php?sn=';
			echo SmrSession::get_new_sn($container1);
			echo '">';
		}
		echo '<img src="images/warp.gif" alt="Warp to #';
		echo $warps[$sector_id];
		echo '" title="Warp to #';
		echo $warps[$sector_id];
		echo '">';
		if($sector_id == $player->sector_id) {
			echo '</a>';
		}
	}
		
	echo '</div>';
			
	// We can skip the rest of the loop if it is unexplored
	if(isset($sector_visited[$sector_id])) {
		echo '</div>';
		++$col;
		continue;
	}

	// exits
	if($sector_cache[$sector_id][0]) {
		echo '<img class="lmlt" src="images/link_hor.gif" alt="" title="">';
	}
	if($sector_cache[$sector_id][2]) {
		echo '<img class="lmlb" src="images/link_hor.gif"alt="" title="">';
	}
	if($sector_cache[$sector_id][3]) {
		echo '<img class="lmll" src="images/link_ver.gif"alt="" title="">';
	}
	if($sector_cache[$sector_id][1]) {
		echo '<img class="lmlr" src="images/link_ver.gif"alt="" title="">';
	}

	// Port
	if(isset($ports[$sector_id]) && !isset($sector_visited[$sector_id])) {
		echo '<div class="lmp">';
		$port_goods = unserialize($ports[$sector_id ]);
		$num_goods = count($port_goods);
		$goods_bought = array();
		$goods_sold = array();
		foreach($port_goods as $good_id => $transaction) {
			if($good_id != 'race_id'){
				if($transaction == "Buy") {
					$goods_bought[] = $good_id;
				}
				else {
					$goods_sold[] = $good_id;
				}
			}
		}
		if($sector_id == $player->sector_id) {
			$container3 = array();
			$container3['url'] = 'skeleton.php';
			$container3['body'] = 'shop_goods.php';
			echo '<a href="';
			echo 'loader2.php?sn=';
			echo SmrSession::get_new_sn($container3);
			echo '">';
		}
		echo '<img src="images/port/buy.gif" alt="Goods Sold" title="Goods Sold">';
		sort($goods_sold);
		foreach($goods_sold as $good_id) {
			if ($player->alignment > -100 && ($good_id == 5 || $good_id == 9 || $good_id == 12)) continue;
			echo '<img src="images/port/';
			echo $good_id;
			echo '.png" alt="';
			echo $goods[$good_id];
			echo '" title="';
			echo $goods[$good_id];
			echo '">';
		}
		echo '<br /><img src="images/port/sell.gif" alt="Goods Bought" title="Goods Bought">';
		sort($goods_bought);
		foreach($goods_bought as $good_id) {
			if ($player->alignment > -100 && ($good_id == 5 || $good_id == 9 || $good_id == 12)) continue;
			echo '<img src="images/port/';
			echo $good_id;
			echo '.png" alt="';
			echo $goods[$good_id];
			echo '" title="';
			echo $goods[$good_id];
			echo '">';
		}
		if($sector_id == $player->sector_id) {
			echo '</a>';
		}
		echo '</div>';
	}

	// Locations
	if(isset($locations[$sector_id]) && !isset($sector_visited[$sector_id])) {
		sort($locations[$sector_id]);
		echo '<div class="lml">';
		foreach($locations[$sector_id] as $location) {
			if($sector_id == $player->sector_id && $location_cache[$location][1]) {
				$container2["body"] = $location_cache[$location][1];
				echo '<a href="';
				echo 'loader2.php?sn=';
				echo SmrSession::get_new_sn($container2);
				echo '">';
			}
			echo '<img src="';
			echo $location_cache[$location][2];
			echo '"alt="';
			echo $location_cache[$location][0];
			echo '" title="';
			echo $location_cache[$location][0];
		  	echo '">';
			if($sector_id == $player->sector_id && $location_cache[$location][1]) {
				echo '</a>';
			}

		}
		echo '</div>';
	}

	echo '</div>';
	++$col;
}

// Tidy up
unset($warps,$players,$sectors,$locations,$goods,$location_cache,$sector_cache,$sector_visited,$adjacent,$sectors_in);

echo '</div></div>';

?>