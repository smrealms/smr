<?php

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

// remove newbie gals
// add newbie to gal name?
//if ($galaxy_id<9 && $rank_id < FLEDGLING && $account->veteran == 'FALSE') {
//	$galaxy_name .= ' - Newbie';
//}

print_topic('CURRENT SECTOR: ' . $player->sector_id . ' (' .$galaxy_name . ')');

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'course_plot.php';
// create menu
$menue_items[] = create_link($container, 'Plot a Course');
$container['body'] = 'map_local.php';
$menue_items[] = create_link($container, 'Local Map');

$menue_items[] = '<a href="' . URL . '/map_galaxy.php" target="_blank">Galaxy Map</a>';

// print it
print_menue($menue_items);

echo '<table cellspacing="0" cellpadding="0" style="width:100%;border:none"><tr><td style="padding:0px;vertical-align:top">';

// *******************************************
// *
// * Sector List
// *
// *******************************************

// Sector links
$db->query('SELECT sector_id,link_up,link_right,link_down,link_left FROM sector WHERE sector_id=' . $player->sector_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');

$db->next_record();
$links = array($db->f('link_up'),$db->f('link_right'),$db->f('link_down'),$db->f('link_left'));

$db->query('SELECT sector_id_1,sector_id_2 FROM warp WHERE (sector_id_1=' . $player->sector_id . ' OR sector_id_2=' . $player->sector_id . ') AND game_id=' . SmrSession::$game_id);

if($db->next_record()) {
	if($db->f('sector_id_1') == $player->sector_id) {
		$links[4] = $db->f('sector_id_2');
	}
	else {
		$links[4] = $db->f('sector_id_1');
	}
}

$unvisited = array();

$db->query('SELECT sector_id FROM player_visited_sector WHERE sector_id IN (' . implode(',', $links) . ') AND account_id=' . SmrSession::$old_account_id . ' AND game_id=' . SmrSession::$game_id);

while($db->next_record()) {
	$unvisited[$db->f('sector_id')] = TRUE;
}

$container1 = array();
$container1['url'] = 'sector_move_processing.php';
$container1['target_page'] = 'current_sector.php';

$container2 = array();
$container2['url'] = 'skeleton.php';
$container2['body'] = 'sector_scan.php';

// FIXME: This is just too ugly
echo '<div style="position:relative;">';

echo '<table cellspacing="0" cellpadding="0" class="csmv">';

// Row one
echo '<tr><td class="csr1c1"';
if(!empty($ship->hardware[HARDWARE_SCANNER])) {
	echo 'rowspan="2" colspan="2"';
}
echo '>&nbsp;</td><td class="csr1c2">';
if(!empty($ship->hardware[HARDWARE_SCANNER])) {
	echo '<div class="cssh">';
	if($links[0]) {
		$container2['target_sector'] = $links[0];
		echo '<a class="cssc" href="loader2.php?sn=' . SmrSession::get_new_sn($container2) . '">SCAN</a>';
	}
	else {
		echo '&nbsp;';
	}
}
else if($links[0]) {
	echo '<div class="css">';
	$container1['target_sector'] = $links[0];
	echo '<a class="css';
	if($player->last_sector_id == $links[0]) {
		echo ' green';
	}
	else if(isset($unvisited[$links[0]])){
		echo ' yellow';
	}
	else {
		echo ' dgreen';
	}
	echo '" href="loader2.php?sn=' . SmrSession::get_new_sn($container1) . '">';
	echo $links[0];
	echo '</a>';
}
else {
	echo '<div class="css">&nbsp;';
}
echo '</div></td><td class="csr1c3"';
if(!empty($ship->hardware[HARDWARE_SCANNER])) {
	echo 'rowspan="2" colspan="2"';
}
echo '>&nbsp;</td></tr>';

if(!empty($ship->hardware[HARDWARE_SCANNER])) {
	echo '<tr><td><div class="css">';
	if($links[0]) {
		$container1['target_sector'] = $links[0];
		echo '<a class="css';
		if($player->last_sector_id == $links[0]) {
			echo ' green';
		}
		else if(isset($unvisited[$links[0]])){
			echo ' yellow';
		}
		else {
			echo ' dgreen';
		}
		echo '" href="loader2.php?sn=' . SmrSession::get_new_sn($container1) . '">';
		echo $links[0];
		echo '</a>';
	}
	else {
		echo '&nbsp;';
	}
	echo '</div></td></tr>';
}

// Row 3
echo '<tr>';

if(!empty($ship->hardware[HARDWARE_SCANNER])) {
	echo '<td class="csr3c1"><div class="cssv">';
	if($links[3]) {
		$container2['target_sector'] = $links[3];
		echo '<a class="cssc" href="loader2.php?sn=' . SmrSession::get_new_sn($container2) . '">S<br>C<br>A<br>N</a>';
	}
	echo '</div></td>';
}

echo '<td ';
if(empty($ship->hardware[HARDWARE_SCANNER])) {
 echo 'class="csr3c1"';
}
echo '><div class="css">';
if($links[3]) {
	$container1['target_sector'] = $links[3];
	echo '<a class="css';
	if($player->last_sector_id == $links[3]) {
		echo ' green';
	}
	else if(isset($unvisited[$links[3]])){
		echo ' yellow';
	}
	else {
		echo ' dgreen';
	}
	echo '" href="loader2.php?sn=' . SmrSession::get_new_sn($container1) . '">';
	echo $links[3];
	echo '</a>';
}
else {
	echo '&nbsp;';
}
echo '</div></td><td>';

$container=array();
$container['url'] = 'skeleton.php';
$container['body'] = 'current_sector.php';
echo '<div class="css"><a class="css dgreen" href="loader2.php?sn=' . SmrSession::get_new_sn($container) . '">';
echo $player->sector_id;
echo '</a></div></td><td ';
if(empty($ship->hardware[HARDWARE_SCANNER])) {
 echo 'class="csr3c5"';
}
echo '><div class="css">';
if($links[1]) {
	$container1['target_sector'] = $links[1];
	echo '<a class="css';
	if($player->last_sector_id == $links[1]) {
		echo ' green';
	}
	else if(isset($unvisited[$links[1]])){
		echo ' yellow';
	}
	else {
		echo ' dgreen';
	}
	echo '" href="loader2.php?sn=' . SmrSession::get_new_sn($container1) . '">';
	echo $links[1];
	echo '</a>';
}
else {
	echo '&nbsp;';
}
echo '</div></td>';

if(!empty($ship->hardware[HARDWARE_SCANNER])) {
	echo '<td class="csr3c5"><div class="cssv">';
	if($links[1]) {
		$container2['target_sector'] = $links[1];
		echo '<a class="cssc" href="loader2.php?sn=' . SmrSession::get_new_sn($container2) . '">S<br>C<br>A<br>N</a>';
	}
	echo '</div></td>';
}

echo '</tr>';

// Row 4
echo '<tr><td class="csr4c1"';
if(!empty($ship->hardware[HARDWARE_SCANNER])) {
	echo 'rowspan="2" colspan="2"';
}
echo '>&nbsp;</td><td ';
if(empty($ship->hardware[HARDWARE_SCANNER])) {
 echo 'class="csr5c1"';
}
echo '><div class="css">';
if($links[2]) {
	$container1['target_sector'] = $links[2];
	echo '<a class="css';
	if($player->last_sector_id == $links[2]) {
		echo ' green';
	}
	else if(isset($unvisited[$links[2]])){
		echo ' yellow';
	}
	else {
		echo ' dgreen';
	}
	echo '" href="loader2.php?sn=' . SmrSession::get_new_sn($container1) . '">';
	echo $links[2];
	echo '</a>';
}
else {
	echo '&nbsp;';
}
echo '</div></td><td class="csr4c3"';
if(!empty($ship->hardware[HARDWARE_SCANNER])) {
	echo 'rowspan="2" colspan="2"';
}
echo '>&nbsp;</td></tr>';

// Row 5
if(!empty($ship->hardware[HARDWARE_SCANNER])) {
	echo '<tr><td class="csr5c1"><div class="cssh">';
	if($links[2]) {
		$container2['target_sector'] = $links[2];
		echo '<a class="cssc" href="loader2.php?sn=' . SmrSession::get_new_sn($container2) . '">SCAN</a>';
	}
	echo '</div></td></tr>';
}

echo '</table>';

// Warps
if(isset($links[4])) {
	echo '<table cellspacing="0" cellpadding="0" class="csmvw';
	if(empty($ship->hardware[HARDWARE_SCANNER])) {
		echo 'ns';
	}
	echo '"><tr><td><div class="css">';
	$container1['target_sector'] = $links[4];
	echo '<a class="csw';
	if($player->last_sector_id == $links[4]) {
		echo ' green';
	}
	else if(isset($unvisited[$links[4]])){
		echo ' yellow';
	}
	else {
		echo ' dgreen';
	}
	echo '" href="loader2.php?sn=' . SmrSession::get_new_sn($container1) . '">';
	echo $links[4];
	echo '</a></div>';
	if(!empty($ship->hardware[HARDWARE_SCANNER])) {
		$container2['target_sector'] = $links[4];
		echo '</td><td>';
		echo '<div class="cssv"><a class="cssc" href="loader2.php?sn=' . SmrSession::get_new_sn($container2) . '">S<br>C<br>A<br>N</a></div>';
	}
	echo '</td></tr></table>';

}


echo '</div><br>';

echo '</td><td style="padding:0px;vertical-align:top;width:32em;">';

// check if we have a course plotted
$db->query('
SELECT course,distance FROM player_plotted_course 
WHERE account_id=' . $player->account_id . '
AND game_id=' . $player->game_id . '
LIMIT 1'
);

if ($db->next_record()) {

	// get the array back
	$path_list	= unserialize(stripslashes($db->f("course")));
	$distance	= $db->f("distance");

	// get the first sector in that list
	$target_sector = array_shift($path_list);

	$container = array();
	$container["url"] = "sector_move_processing.php";
	$container["target_page"] = "current_sector.php";
	$container["target_sector"] = $target_sector;

	print_button($container, 'Follow plotted course - ' . $target_sector . ' (' . $distance . ')');

	if (!empty($ship->hardware[HARDWARE_SCANNER])) {
		echo '&nbsp;&nbsp;&nbsp;';
		$container = array();
		$container["url"]			= "skeleton.php";
		$container["body"]			= "sector_scan.php";
		$container["target_sector"] = $target_sector;
		print_button($container, 'Scan');
	}
	echo '<br /><br />';
}


//any ticker news?
if($player->ticker != "FALSE" && $player->ticker != "BLOCK") {
	$max = time() - 60;
	if($player->ticker == "NEWS") {
		$text = '';
		//get recent news (5 mins)
		
		$db->query("SELECT time,news_message FROM news WHERE game_id = $player->game_id AND time >= $max ORDER BY time DESC LIMIT 4");
		if ($db->nf()) {
			while($db->next_record()){
				$text .= date("n/j/Y g:i:s A", $db->f("time"));
				$text .= ': &nbsp;';
				$text .= stripslashes($db->f("news_message"));
				$text .= "<br><br>";
			}
		} else
			$text = "Nothing to report";
	} else if ($player->ticker=="SCOUT") {
		$text = '';
		//get people who have blockers
		$db->query('SELECT * FROM player_has_ticker WHERE type="block" AND game_id = ' . $player->game_id);
		$temp=array();
		$temp[] = 0;
		while ($db->next_record()) $temp[] = $db->f("account_id");
		$query = 'SELECT message_text,send_time FROM message
					WHERE account_id=' . $player->account_id . '
					AND game_id=' . $player->game_id . '
					AND message_type_id=' . MSG_SCOUT . '
					AND send_time>=' . $max . '
					AND sender_id NOT IN (' . implode(',', $temp) . ')
					ORDER BY send_time DESC
					LIMIT 4';
		$db->query($query);
		unset($temp);
		if ($db->nf()) {
			while($db->next_record()){
				$text .= date("n/j/Y g:i:s A", $db->f("send_time"));
				$text .= ': &nbsp;';
				$text .= stripslashes($db->f("message_text"));
				$text .= "<br>";
			}
		} else
			$text = "Nothing to report";
	}

	echo '<div style="overflow:auto;height:8em;border:2px solid #0b8d35;text-align:left">';
	echo $text;
	echo '</div><br>';
	$player->last_ticker_update = time();
	$player->update();
}

// *******************************************
// *
// * Force and other Results
// *
// *******************************************
if ($player->turns < 50)
	$msg = "<span class=\"red\">WARNING</span>: Low turns!";
if ($player->newbie_turns)
	$msg = "[Protection Check]";
$db->query("SELECT * FROM location WHERE location_type_id = ".FED." AND sector_id = $player->sector_id AND game_id = $player->game_id LIMIT 1");
if ($db->next_record())
	$msg = "[Protection Check]";
	//$db->query("REPLACE INTO sector_message (game_id, account_id, message) VALUES ($player->game_id, $player->account_id, '[Protection Check]')");
$db->query("SELECT * FROM sector_message WHERE account_id = $player->account_id AND game_id = $player->game_id");
if ($db->next_record()) {
	$msg = stripslashes($db->f("message"));
	$db->query("DELETE FROM sector_message WHERE account_id = $player->account_id AND game_id = $player->game_id");
}
//messages sent to CS should take priority over protection messages
if (isset($var["msg"]))	$msg = $var["msg"];
if ($msg == "[Force Check]") {
	$db->query("SELECT * FROM force_refresh WHERE refresh_at >= " . time() . " AND sector_id  = $player->sector_id AND game_id = $player->game_id ORDER BY refresh_at DESC LIMIT 1");
	if ($db->next_record()) {
		$remainingTime = $db->f("refresh_at") - time();
		$msg = "<span class=\"green\">REFRESH</span>: All forces will be refreshed in $remainingTime seconds.";
		$db->query("REPLACE INTO sector_message (game_id, account_id, message) VALUES ($player->game_id, $player->account_id, '[Force Check]')");
	} else $msg = "<span class=\"green\">REFRESH</span>: All forces have finished refreshing.";
}
if ($msg == "[Protection Check]") {
	if ($player->newbie_turns) {
		if ($player->newbie_turns < 25) {
			$msg = "<span class=\"blue\">PROTECTION</span>: You are almost out of <span class=\"green\">NEWBIE</span> protection.";
			$db->query("REPLACE INTO sector_message (game_id, account_id, message) VALUES ($player->game_id, $player->account_id, '[Protection Check]')");
		} else
			$msg = "<span class=\"blue\">PROTECTION</span>: You are under <span class=\"green\">NEWBIE</span> protection.";
	} elseif ($player->is_fed_protected()) {
		$msg = "<span class=\"blue\">PROTECTION</span>: You are under <span class=\"blue\">FEDERAL</span> protection.";
		$db->query("REPLACE INTO sector_message (game_id, account_id, message) VALUES ($player->game_id, $player->account_id, '[Protection Check]')");
	} else
		$msg = "<span class=\"blue\">PROTECTION</span>: You are <span class=\"red\">NOT</span> under protection.";
}
if ($player->account_id == 2)
{
	$db->query("SELECT * FROM player WHERE account_id = 10106 AND game_id = 23 LIMIT 1");
	if ($db->next_record()) $msg .= '<br />In Sector:'.$db->f('sector_id').'<br />';
}
//error msgs take precedence
if (isset($var['errorMsg'])) $msg = $var['errorMsg'];
if (isset($msg)) {
	echo $msg;
	echo "<br /><br />";
}

// *******************************************
// *
// * Trade Result
// *
// *******************************************

//You have sold 300 units of Luxury Items for 1738500 credits. For your excellent trading skills you receive 220 experience points!
if (!empty($var["traded_xp"]) ||
	!empty($var["traded_amount"]) ||
	!empty($var["traded_good"]) ||
	!empty($var["traded_credits"])) {

	echo 'You have just ';
	echo $var['traded_transaction'];
	echo ' <span style="color:yellow;">';
	echo $var['traded_amount'];
	echo '</span> units of <span style="color:yellow;">';
	echo $var['traded_good'];
	echo '</span> for <span style="color:yellow;">';
	echo $var['traded_credits'];
	echo '</span> credits.<br />';

	if ($var["traded_xp"] > 0) {
		echo 'Your excellent trading skills have gained you <font color="blue">';
		echo $var["traded_xp"];
		echo ' </font>experience points!<br />';
	}

	echo '<br />';
}

echo '</td></tr></table>';

// *******************************************
// *
// * Planets
// *
// *******************************************

$db->query('SELECT planet_name,inhabitable_time FROM planet WHERE sector_id=' . $player->sector_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');

if ($db->next_record()) {

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'planet_examine.php';

	echo '
	<table cellspacing="0" cellpadding="0" class="standard csl"><tr><th>Planet</th><th>Option</th></tr><tr><td><img align="left" src="images/planet.gif" alt="Planet" title="Planet">&nbsp;';

	echo stripslashes($db->f('planet_name'));

	if ($db->f('inhabitable_time') <= time()) {
		echo ' <span class="green">Inhabitable</span>';
	}
	else {
		echo ' <span class="red">Inhabitable</span>';
	}

	echo '</td><td class="center nowrap shrink">';

	print_button($container,"Examine");
	echo '</td></tr></table><br />';
}


// *******************************************
// *
// * Ports
// *
// *******************************************

// We need the races later for the players, so pull them out here
$db->query('SELECT race_id,race_name FROM race');
while($db->next_record()) {
	$races[$db->f('race_id')] = $db->f('race_name');
}

// Cache good names for later
$db->query('SELECT good_id,good_name FROM good');
while($db->next_record()) {
	$goods[$db->f('good_id')] = $db->f('good_name');
}

$db->query('SELECT `race_id`,`attack_started`,`level`,`refresh_defense`,`credits`,`upgrade` FROM `port` WHERE `sector_id`=' . $player->sector_id . ' AND `game_id`=' . SmrSession::$game_id . ' LIMIT 1');

if ($db->next_record()) {
	$player->get_relations();
	echo '<table cellspacing="0" cellpadding="0" class="standard csl"><tr><th colspan="2">Port</th><th>Option</th></tr>';

	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'trader_relations.php';

	$refresh_defense = $db->f('refresh_defense');
	$attack_started = $db->f('attack_started');
	$credits = $db->f('credits');
	$upgrade = $db->f('upgrade');
	$race_id = $db->f('race_id');
	$relation = $player->relations_global_rev[$race_id] + $player->relations[$race_id];

	echo '<tr><td style="border-right:none">';
	
	print_link($container, get_colored_text($relation, $races[$race_id]));

	echo ' Port ';
	echo $player->sector_id;
	echo ' (Level ';
	echo $db->f('level');
	echo ')<br />';

	// Goods
	$db->query('SELECT good_id,transaction FROM port_has_goods WHERE sector_id=' . $player->sector_id . ' AND game_id=' . SmrSession::$game_id);
	$goods_bought = array();
	$goods_sold = array();
	while($db->next_record()) {
		if($db->f('transaction') == 'Buy') {
			$goods_bought[] = $db->f('good_id');
		}
		else {
			$goods_sold[] = $db->f('good_id');
		}
	}

	echo '<img src="images/port/buy.gif" alt="Goods Sold" title="Goods Sold">';
	sort($goods_sold);
	foreach($goods_sold as $good_id) {
		if ($player->alignment > -100 && ($good_id == 5 || $good_id == 9 || $good_id == 12)) continue;
		echo '<img src="images/port/' . $good_id . '.png" alt="' . $goods[$good_id] . '" title="' . $goods[$good_id] . '">';
	}
	echo '<br /><img src="images/port/sell.gif" alt="Goods Bought" title="Goods Bought">';
	sort($goods_bought);
	foreach($goods_bought as $good_id) {
		if ($player->alignment > -100 && ($good_id == 5 || $good_id == 9 || $good_id == 12)) continue;
		echo '<img src="images/port/' . $good_id . '.png" alt="' . $goods[$good_id] . '" title="' . $goods[$good_id] . '">';
	}

	// Graph
	echo '</td><td style="padding-right:1px;border-left:none;vertical-align:bottom;text-align:right">';
	// Upgrade indicator
	$upgrade_percentage = ceil($upgrade / 100000);
	if($upgrade_percentage > 100) $upgrade_percentage=100;
	$upgrade = ceil(32 * ($upgrade_percentage/100));
	if($upgrade == 0) $upgrade = 1;
	echo '<img style="height:' . $upgrade . 'px;width:6px;border:2px solid #000000;border-bottom:none;" src="images/green.gif" alt="Upgrade" title="Upgrade">';

	$cash_percentage = ceil($credits / 3200000);
	if($cash_percentage > 100) $cash_percentage = 100;
	$cash = ceil(32 * ($cash_percentage/100));
	if($cash == 0) $cash = 1;
	echo '<img style="height:' . $cash . 'px;width:6px;border:2px solid #000000;border-bottom:none;" src="images/blue.gif" alt="Upgrade" title="Credits">';

	$time = time();
	if ($refresh_defense > $time) {
		$defense_percentage = ceil(100 * ($refresh_defense - $time) / ($refresh_defense - $attack_started));
		if($defense_percentage > 100) $defense_percentage = 100;
	}
	else $defense_percentage = 1;
	$defense = ceil(32 * ($defense_percentage/100));
	echo '<img style="height:' . $defense . 'px;width:6px;border:2px solid #000000;border-bottom:none;" src="images/red.gif" alt="Defense" title="Defense">';

	echo '</td><td class="center shrink nowrap">';

	$container['body'] = 'shop_goods.php';

	if ($refresh_defense > time()) {
		echo '<span class="red bold">ALERT!!</span>';
	}
	else if($relation <= -300){
		echo '<span class="red bold">WAR!!</span>';
	}
	else {
		print_button($container, 'Trade');
	}

	echo '&nbsp;';
	$container['body'] = 'port_attack_warning.php';

	print_button($container,'Raid');

	echo '</td></tr>';

	echo '</table><br />';
}


// *******************************************
// *
// * Locations
// *
// *******************************************

$db->query('SELECT location_type_id FROM location WHERE sector_id=' . $player->sector_id . ' AND game_id=' . SmrSession::$game_id);

while($db->next_record()) {
	$locations[] = $db->f('location_type_id');
}

// We get all the locations at the same time
if(isset($locations)) {
	$db->query('SELECT location_type_id,location_name,location_processor,location_image FROM location_type WHERE location_type_id IN (' . implode(',', $locations) . ') LIMIT ' . count($locations));

	echo '<table cellspacing="0" cellpadding="0" class="standard csl">';
	echo '<tr><th>Location</th>';

	$num_locations = count($locations) - 1;
	if($num_locations || $locations[0] != 201) {
		echo '<th>Option</th>';
	}

	$container = array();
	$container['url'] = 'skeleton.php';

	while($db->next_record()) {
		$location_name = stripslashes($db->f('location_name'));
		echo '<tr><td';
		if(!$db->f('location_processor') && $num_locations) {
			echo ' colspan="2"';
		}
		echo '>';
		echo '<img align="left"src="' . $db->f('location_image') . '" alt="' . $location_name . '" title="' . $location_name . '">&nbsp;';
		echo $location_name;
		if($db->f('location_processor')) {
			echo '</td><td class="shrink nowrap">';
			$container['body'] = $db->f('location_processor');
			print_button($container,'Examine');
		}
		echo '</td></tr>';
	}
	echo '</table><br>';
}

//echo '</div></td></tr></table><br>';



$query = '
SELECT
sector_has_forces.owner_id as account_id,
sector_has_forces.combat_drones as combat_drones,
sector_has_forces.scout_drones as scout_drones,
sector_has_forces.mines as mines,
sector_has_forces.expire_time as expire_time,
player.player_id as player_id,
player.alliance_id as alliance_id,
player.alignment as alignment,
player.player_name as player_name
FROM sector_has_forces,player';

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
player.account_id=sector_has_forces.owner_id
AND player.game_id=' . SmrSession::$game_id . '
AND sector_has_forces.sector_id=' . $player->sector_id . '
AND sector_has_forces.game_id=' . SmrSession::$game_id . '
ORDER BY sector_has_forces.expire_time ASC';

$db->query($query);

while($db->next_record()) {
	$forces[$db->f('account_id')] = array(
					$db->f('player_id'),
					stripslashes($db->f('player_name')),
					$db->f('alliance_id'),
					$db->f('alignment'),
					$db->f('combat_drones'),
					$db->f('scout_drones'),
					$db->f('mines'),
					$db->f('expire_time')
				);
	if($db->f('alliance_id')) {
		$alliances[$db->f('alliance_id')] = TRUE;
	}
}

// Players in adjacent sector (UGLY! Let's store rank in the db in SMR2)
$query = '
SELECT 
player.account_id as account_id,
player.player_id as player_id,
player.player_name as player_name,
player.race_id as race_id,
player.alliance_id as alliance_id,
player.alignment as alignment,
player.ship_type_id as ship_type_id,
player.experience as experience 
FROM player';

// remove newbie gals
//if ($galaxy_id < 9) {
//	$query .= ',account_has_stats,account	
//			WHERE account.account_id = player.account_id
//			AND account_has_stats.account_id = player.account_id';
//}
//HIDEN_PLAYERS is defined in config.inc
$query .= $query2 . 'player.sector_id=' . $player->sector_id . '
	AND player.account_id!=' . SmrSession::$old_account_id . ' 
	AND player.game_id=' . SmrSession::$game_id . ' 
	AND player.land_on_planet="FALSE" 
	AND player.last_active>' .  (time() - 259200) . '
	AND player.account_id NOT IN (' . implode(',', $HIDDEN_PLAYERS) . ')
	ORDER BY player.last_active DESC';

$db->query($query);

while($db->next_record()) {
	$players[$db->f('account_id')] = array(
					$db->f('player_id'),
					stripslashes($db->f('player_name')),
					$db->f('race_id'),
					$db->f('alliance_id'),
					$db->f('alignment'),
					$db->f('ship_type_id'),
					$db->f('experience'),
					0,0,0,0
				);

	if($db->f('alliance_id')) {
		$alliances[$db->f('alliance_id')] = TRUE;
	}
	$ships[$db->f('ship_type_id')] = TRUE;
}

// Grab the level requirements
if(isset($players)) {
	$num_players_orig = count($players);
	$db->query('SELECT level_id,requirement FROM level ORDER BY requirement DESC');
	while($db->next_record()) {
		$levels[$db->f('level_id')] = $db->f('requirement');
	}

	// Figure out everyone's level
	$num_players = count($players);
	$player_ids = array_keys($players);
	$num_levels = count($levels);
	$level_ids = array_keys($levels);

	for($i=0;$i<$num_players;++$i) {
		for($j=0;$j<$num_levels;++$j) {
			if($levels[$level_ids[$j]] <= $players[$player_ids[$i]][6]) {
				$players[$player_ids[$i]][7] = $level_ids[$j];
				break;
			}
		}
	}

	// Remove any cloaked ships
	$db->query('SELECT * FROM ship_is_cloaked WHERE account_id IN (' . implode(',',array_keys($players)) . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . count($players));

	while($db->next_record() && !in_array($player->account_id, $HIDDEN_PLAYERS)) {
		if($players[$db->f('account_id')][7] >= $player->level_id) {
			unset($players[$db->f('account_id')]);
		}
	}
	$num_player_uncloaked = count($players);

	if($num_player_uncloaked == 0) {
		unset($players);
	}
	unset($levels);
}



// Get any illusory ships
if(isset($players)) {
	$db->query('SELECT * FROM ship_has_illusion WHERE account_id IN (' . implode(',',array_keys($players)) . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . count($players));

	while($db->next_record()) {
		$players[$db->f('account_id')][12] = array(
												$db->f('ship_type_id'),
												$db->f('attack'),
												$db->f('defense')
											);
		// Add the ship to the ship array so we get the name
		$ships[$db->f('ship_type_id')] = TRUE;
	}
}

// Named ships and ship images
if(isset($players)) {
	$db->query('SELECT * FROM ship_has_name WHERE account_id IN (' . implode(',',array_keys($players)) . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . count($players));

	while($db->next_record()) {
		$players[$db->f('account_id')][13] = $db->f('ship_name');
	}
}

// Get everyone's ships
if(isset($players)) {
	$db->query('SELECT ship_type_id,ship_name FROM ship_type WHERE ship_type_id IN (' . implode(',',array_keys($ships)) . ') LIMIT ' . count($ships));

	while($db->next_record()) {
		$ships[$db->f('ship_type_id')] = $db->f('ship_name');
	}
}

// Get everyone's ship hardware
if(isset($players)) {
	$db->query('SELECT * FROM ship_has_hardware WHERE account_id IN (' . implode(',',array_keys($players)) . ') AND (hardware_type_id=1 OR hardware_type_id=2 OR hardware_type_id=4) AND game_id=' . SmrSession::$game_id);

	while($db->next_record()) {
		switch($db->f('hardware_type_id')) {
		case(1):
			$players[$db->f('account_id')][8] = $db->f('amount');
			break;
		case(2):
			$players[$db->f('account_id')][9] = $db->f('amount');
			break;
		case(4):
			$players[$db->f('account_id')][10] = $db->f('amount');
			break;
		}
	}
}

// Grab everyone's ship weapons
if(isset($players)) {
	$db->query('SELECT * FROM ship_has_weapon WHERE account_id IN (' . implode(',',array_keys($players)) . ') AND game_id=' . SmrSession::$game_id);

	while($db->next_record()) {
		$weapons[$db->f('weapon_type_id')] = TRUE;
		$players[$db->f('account_id')][11][] = $db->f('weapon_type_id');
	}
}

// Get the weapon stats
if(isset($weapons)) {
	$db->query('SELECT weapon_type_id,shield_damage,armor_damage FROM weapon_type WHERE weapon_type_id IN (' . implode(',',array_keys($weapons)) . ') LIMIT ' . count($weapons));
	
	while($db->next_record()) {
		$weapons[$db->f('weapon_type_id')] = $db->f('shield_damage') + $db->f('armor_damage');
	}
}

// Grab any alliance names we may need
if(isset($alliances)) {

	$db->query('SELECT alliance_id,alliance_name FROM alliance WHERE alliance_id IN (' . implode(',',array_keys($alliances)) . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . count($alliances));

	while($db->next_record()) {
		$alliances[$db->f('alliance_id')] = stripslashes($db->f('alliance_name'));
	}
}

$alliances[0] = 'None';

// *******************************************
// *
// * T R A D E R
// *
// *******************************************
//get current players treaties
$db->query("SELECT alliance_id_1, alliance_id_2, trader_assist, trader_defend, trader_nap, forces_nap FROM alliance_treaties
			WHERE (alliance_id_1 = $player->alliance_id OR alliance_id_2 = $player->alliance_id)
			AND game_id = $player->game_id
			AND (trader_nap = 1 OR forces_nap = 1)
			AND official = 'TRUE'");
$treaties=array();
define('NAP',0);
define('ASSIST',1);
define('DEFEND',2);
define('FORCES',3);
if ($player->alliance_id) $treaties[FORCES][$player->alliance_id] = TRUE;
while ($db->next_record()) {
	if ($db->f("alliance_id_1") == $player->alliance_id) {
		if ($db->f("forces_nap")) $treaties[FORCES][$db->f("alliance_id_2")] = TRUE;
		if ($db->f("trader_nap")) $treaties[NAP][$db->f("alliance_id_2")] = TRUE;
		if ($db->f("trader_defend")) $treaties[DEFEND][$db->f("alliance_id_2")] = TRUE;
		if ($db->f("trader_assist")) $treaties[ASSIST][$db->f("alliance_id_2")] = TRUE;
	} else {
		if ($db->f("forces_nap")) $treaties[FORCES][$db->f("alliance_id_1")] = TRUE;
		if ($db->f("trader_nap")) $treaties[NAP][$db->f("alliance_id_1")] = TRUE;
		if ($db->f("trader_defend")) $treaties[DEFEND][$db->f("alliance_id_1")] = TRUE;
		if ($db->f("trader_assist")) $treaties[ASSIST][$db->f("alliance_id_1")] = TRUE;
	}
}
if(isset($players)) {
	$container1 = array();
	$container1["url"]		= "skeleton.php";
	$container1["body"]		= "trader_examine.php";

	$container2 = array();
	$container2["url"]		= "skeleton.php";
	$container2["body"]		= "trader_search_result.php";

	$container3 = array();
	$container3["url"] = "skeleton.php";
	$container3["body"] = "council_list.php";
//<h2>Players in sector</h2><br>
	echo '<table class="standard fullwidth" cellspacing="0"><tr><th colspan="5" style="background:#550000;color:#80C870">Ships (';
	echo count($players);
	echo ')</th><tr><th>Trader</th><th>Ship</th><th>Rating</th><th>Level</th><th >Option</th></tr>';
	foreach($players as $account_id => $current_player) {
		echo '<tr>';
		echo '<td>';
		$container2["player_id"]	= $current_player[0];
		print_link($container2, get_colored_text($current_player[4],$current_player[1] . ' (' . $current_player[0] . ')'));
		echo ' (';
		if($current_player[3]) {
			$container = array();
			$container["url"]			= "skeleton.php";
			$container["body"]			= "alliance_roster.php";
			$container["alliance_id"]	= $current_player[3];
			print_link($container, $alliances[$current_player[3]]);
		}
		else {
			echo $alliances[$current_player[3]];
		}
		echo ') ';
		if (isset($treaties[NAP][$current_player[3]])) echo '<img src="images/nap.gif" alt="Your alliance has a Non Agression Pact with this alliance."> ';
		if (isset($treaties[DEFEND][$current_player[3]])) echo '<img src="images/defensive.gif" alt="Your alliance has a Defensive Pact with this alliance."> ';
		if (isset($treaties[ASSIST][$current_player[3]])) echo '<img src="images/offensive.gif" alt="Your alliance has an Offensive Pact with this alliance."> ';
		echo '</td>';
		echo '<td>';
		if(isset($current_player[13])) {
			if(!stristr($current_player[13],'<img')){
				echo $current_player[13];
			}
			else if($account->images == "Yes"){
				echo $current_player[13];
			}
		}
		if(!isset($current_player[12])){
			echo $ships[$current_player[5]];
		}
		else {
			echo $ships[$current_player[12][0]];
		}

		echo '</td>';
		echo '<td class="shrink center nowrap">';
		if(!isset($current_player[12])){
			// Rating
			$total_damage = 0;
			if(isset($current_player[11])){
				foreach($current_player[11] as $weapon) {
				$total_damage += $weapons[$weapon];
				}
			}
			$maxDronesPercent = (35 + $current_player[7] * .6 + ($current_player[7] - 1) * .4 + 15) * .01;
			$maxDrones = $maxDronesPercent * $current_player[10];
			$attack_rating = round((($total_damage + $maxDrones * 2) / 40));
			echo $attack_rating;
			echo ' / ';
			echo round(($current_player[8] + $current_player[9] + ($current_player[10] * 3)) * 0.01);
		}
		else {
			echo $current_player[12][1];
			echo ' / ';
			echo $current_player[12][2];
		}
		echo '</td>';
		echo '<td class="shrink center nowrap">';
	//	$container3["race_id"] = $current_player[2];
//		$container3["race_name"] = $races[$current_player[2]];
		//print_link($container3, get_colored_text($player->relations_global[$current_player[2]], $races[$current_player[2]]));
		echo $current_player[7];
		echo '</td>';
		echo '<td class="shrink center nowrap">';
		$container1['target'] = $account_id;
		print_button($container1, 'Examine');
		echo '</td>';
		echo '</tr>';
	}

	echo '</table><br>';
	unset($players,$ships,$races,$weapons);
}
else if(isset($num_players_orig) && ($num_players_orig != $num_players_uncloaked)) {
//<h2>Players in sector</h2><br>
	echo '<span class="red bold">WARNING:</span> Sensors have detected the presence of cloaked vessels in this sector<br><br>';
}

// *******************************************
// *
// * F O R C E S
// *
// *******************************************

if(isset($forces)) {
	$container1 = array();
	$container1["url"]		= "skeleton.php";
	$container1["body"]		= "trader_search_result.php";
//<h2>Forces in sector</h2><br>
	echo '<table class="standard fullwidth" cellspacing="0"><tr><th colspan="6" style="background:#000055;color:#80C870">Forces (' . count($forces) . ')</th></tr><tr><th>Mines</th><th>Combat</th><th>Scout</th><th>Expiry</th><th>Owner</th><th>Option</th></tr>';
	foreach ($forces as $account_id => $current_force) {
		echo '<tr>';
		echo '<td class="center shrink nowrap">';
		$allow_alter = ($account_id == $player->account_id || ($current_force[2] && isset($treaties[FORCES][$current_force[2]])));
		if ($allow_alter) {
			$container = array();
			$container["url"]		= "forces_drop_processing.php";
			$container["owner_id"]		= $account_id;
			$container["drop_mines"]	= 1;
			print_link($container, "&nbsp;<b>+</b>&nbsp;");
			echo $current_force[6];
			$container = array();
			$container["url"]		= "forces_drop_processing.php";
			$container["owner_id"]		= $account_id;
			$container["take_mines"]	= 1;
			print_link($container, "&nbsp;<b>-</b>&nbsp;");
		}
		else
			echo $current_force[6];
		echo '</td>';
		echo '<td class="center shrink nowrap">';
		if ($allow_alter) {
			$permit_mass_refresh = TRUE;
			$container = array();
			$container["url"] = "forces_drop_processing.php";
			$container["owner_id"] = $account_id;
			$container["drop_combat_drones"]	= 1;
			print_link($container, "&nbsp;<b>+</b>&nbsp;");
			echo $current_force[4];
			$container = array();
			$container["url"] = "forces_drop_processing.php";
			$container["owner_id"] = $account_id;
			$container["take_combat_drones"] = 1;
			print_link($container, "&nbsp;<b>-</b>&nbsp;");
		}
		else
			echo $current_force[4];
		echo '</td>';
		echo '<td class="center shrink nowrap">';
		if ($allow_alter) {
			$container = array();
			$container["url"]		= "forces_drop_processing.php";
			$container["owner_id"]		= $account_id;
			$container["drop_scout_drones"]	= 1;
			print_link($container, "&nbsp;<b>+</b>&nbsp;");
			echo $current_force[5];
			$container = array();
			$container["url"]		= "forces_drop_processing.php";
			$container["owner_id"]		= $account_id;
			$container["take_scout_drones"]	= 1;
			print_link($container, "&nbsp;<b>-</b>&nbsp;");
		}
		else
			echo $current_force[5];
		echo '<td class="shrink nowrap center">';
		if ($allow_alter)
			echo '<span class="green">' . date('n/j/Y\<b\r /\>g:i:s A', $current_force[7]) . '</span>';
		else
			echo '<span style="color:red;"><b>WAR</b></span>';
		echo '</td>';
		echo '<td>';
		$container1["player_id"] = $current_force[0];
		print_link($container1, get_colored_text($current_force[3],$current_force[1] . ' (' . $current_force[0] . ')'));
		echo '<br />(';
		if($current_force[2]) {
			$container = array();
			$container["url"]			= "skeleton.php";
			$container["body"]			= "alliance_roster.php";
			$container["alliance_id"]	= $current_force[2];
			print_link($container, $alliances[$current_force[2]]);
		}
		else
			echo $alliances[$current_force[2]];
		echo ')</td>';
		print("<td align=\"center\" class=\"shrink nowrap center\">");
		$container = array();
		$container["url"] = "skeleton.php";
		if ($allow_alter)
			$container["body"] = "forces_drop.php";
		else
			$container["body"] = "forces_examine.php";
		$container["owner_id"] = $account_id;
		print_button($container, "Examine");
		if ($allow_alter) {
			echo '&nbsp;';
			$container = array();
			$container["url"]	= "forces_refresh_processing.php";
			$container["owner_id"]	= $account_id;
			print_button($container, "Refresh");
		}
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
	unset($forces,$alliances);
}

if(isset($permit_mass_refresh)) {
	$container = array();
	$container["url"]			= "forces_mass_refresh.php";
	$container["alliance_id"]		= $player->alliance_id;
	echo '<div align="center"><br />';
	print_button($container, 'Refresh All');
	echo '</div>';
}
$temp = array();
$time = time();
$db->query("SELECT * FROM force_refresh WHERE sector_id = $player->sector_id " .
		"AND game_id = $player->game_id AND refresh_at <= $time");
while ($db->next_record())
	$temp[$db->f("owner_id")] = array($db->f("num_forces"), $db->f("refresh_at"));

foreach ($temp as $owner => $totalArr) {
	$total = $totalArr[0];
	$days = ceil($total / 10);
	if ($days > 5) $days = 5;
	$expire = $totalArr[1] + ($days * 86400);
	$db->query("UPDATE sector_has_forces SET expire_time = $expire WHERE game_id = $player->game_id AND sector_id = $player->sector_id AND " . 
				"owner_id = $owner");
	$db->query("DELETE FROM force_refresh WHERE game_id = $player->game_id " .
			"AND sector_id = $player->sector_id AND owner_id = $owner");
}
unset($temp);
?>
