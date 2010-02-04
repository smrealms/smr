<?
echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="css/classic.css">
<title>Space Merchant Realms</title>
<meta http-equiv="pragma" content="no-cache">
<script type="text/javascript" src="js/smr.js"></script>
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="css/ie_specific.css">
<![endif]-->
<style type="text/css">
	body {
		font-size:' . $account->fontsize . '%;
	}
</style>
</head>
<body>
<table align="center" cellspacing="0" cellpadding="0" class="m">
<tr>
<td rowspan="2" class="l0"><div class="l1"><div class="l2">
';

echo '<span style="color:yellow">' . date('n/j/Y\<b\r /\>g:i:s A') . '</span><br><br>';

$container = array();
$container['url'] = 'skeleton.php';

if (SmrSession::$game_id > 0) {
	echo '<big><b>';
	if ($player->land_on_planet == 'FALSE') {
		$container['body'] = 'current_sector.php';
		print_link($container, 'Current Sector');
		echo '<br>';
		$container['body'] = 'map_local.php';
		print_link($container, 'Local Map');
	}
	else {
		$container['url'] = 'planet_main_processing.php';
		$container['body'] = '';
		print_link($container, 'Planet Main');
	}
	echo '<br>';
	$container['url'] = 'skeleton.php';
	$container['body'] = 'course_plot.php';
	print_link($container, 'Plot a Course');
	echo '</b></big><br>';
	echo '<a href="' . URL . '/map_galaxy.php" target="_blank">Galaxy Map</a><br><br>';
	$container['url'] = 'skeleton.php';
	$container['body'] = 'trader_status.php';
	print_link($container, 'Trader');
	echo '<br>';
	if ($player->alliance_id > 0) {
		$container['body'] = 'alliance_mod.php';
	} else {
		$container['body'] = 'alliance_list.php';
		$container['order'] = 'alliance_name';
	}
	print_link($container, 'Alliance');
	echo '<br>';
	$container['body'] = 'combat_log_viewer.php';
	print_link($container, 'Combat Logs');
	echo '<br><br>';
	$container['body'] = 'trader_planet.php';
	print_link($container, 'Planet');
	echo '<br>';
	$container['body'] = 'forces_list.php';
	print_link($container, 'Forces');
	echo '<br><br>';
	$container['body'] = 'message_view.php';
	print_link($container, 'Messages');
	echo '<br>';
	$container['body'] = 'news_read_current.php';
	print_link($container, 'Read News');
	echo '<br>';
	$container['body'] = 'galactic_post_read.php';
	print_link($container, 'Galactic Post');
	echo '<br><br>';
	$container['body'] = 'trader_search.php';
	print_link($container, 'Search for Trader');
	echo '<br>';
	$container['body'] = 'current_players.php';
	print_link($container, 'Current Players');
	echo '<br><br>';
	$container['body'] = 'rankings_player_experience.php';
	print_link($container, 'Rankings');
	echo '<br>';
	$container['body'] = 'hall_of_fame_new.php';
	print_link($container, 'Hall of Fame');
	echo '<br>';
	$container['body'] = "hall_of_fame_new.php";
	$container['game_id'] = $player->game_id;
	print_link($container, "Current HoF");
	unset($container['game_id']);
	echo '<br><br>';
}

if (SmrSession::$old_account_id > 0 && empty($var['logoff'])) {
	$container['body'] = '';
	$container['url'] = 'game_play_preprocessing.php';
	echo '<a href="loader.php?sn=' . SmrSession::addLink($container) . '">Play Game</a>';
	echo '<br>';
	$container['url'] = 'logoff_preprocessing.php';
	echo '<a href="loader.php?sn=' . SmrSession::addLink($container) . '">Logoff</a>';
}
else {
	echo '<a href="login.php">Login</a><br>';
}

echo '<br><br>';
echo '<a href="' . URL . '/manual.php" target="_blank">Manual</a><br>';
//$container['url'] = 'skeleton.php';
//$container['body'] = 'preferences.php';
//print_link($container, 'Preferences');
//echo '<br>';
/*
$container['body'] = '';
$container['url'] = 'mgu_create.php';
print_link($container, 'DL MGU Maps');
echo '<br>';
*/
if (SmrSession::$game_id > 0) {
	$container['body'] = '';
	$container['url'] = 'mgu_create_new.php';
	print_link($container, 'DL MGU Maps');
	echo '<br>';
}
$container['url'] = 'skeleton.php';
//$container['body'] = 'album_edit.php';
//print_link($container, 'Edit Photo');
//echo '<br>';
echo '<a href="' . URL . '/album/" target="_blank">View Album</a><br><br>';

$container['body'] = 'bug_report.php';
print_link($container, 'Report a Bug');
echo '<br>';
$container['body'] = 'contact.php';
print_link($container, 'Contact Form');
echo '<br><br><b>';

if (SmrSession::$game_id > 0) {
	echo '<big>';
	$container['body'] = 'chat_rules.php';
	print_link($container, 'IRC Chat');
	echo '</big><br>';
}

echo '<a href="http://smrcnn.smrealms.de/viewtopic.php?t=3515/album/" target="_blank">User Policy</a><br>';
echo '<a href="http://smrcnn.smrealms.de/" target="_blank">WebBoard</a></b><br>';
$container['body'] = 'donation.php';
print_link($container, 'Donate');

echo '
</div></div>
</td>
<td colspan="2" class="m0"><div class="m1"><div class="m2">
';

include(get_file_loc($var["body"]));

echo '
</div></div>
</td>
<td rowspan="2" class="r0"><div class="r1"><div class="r2">
';
if (SmrSession::$game_id != 0){
	$under_attack_shields = ($ship->old_hardware[HARDWARE_SHIELDS] != $ship->hardware[HARDWARE_SHIELDS]);
	$under_attack_armor = ($ship->old_hardware[HARDWARE_ARMOR] != $ship->hardware[HARDWARE_ARMOR]);
	$under_attack_drones = ($ship->old_hardware[HARDWARE_COMBAT] != $ship->hardware[HARDWARE_COMBAT]);

	if ($under_attack_shields || $under_attack_armor || $under_attack_drones) {
		echo '
			<div id="attack_warning" class="attack_warning"><nobr>You are under attack!</nobr></div>
			<script type="text/javascript">
			SetBlink();
			</script>
			';
		$ship->mark_seen();
	}

	$db->query('SELECT message_type_id,COUNT(*) FROM player_has_unread_messages WHERE account_id=' . $player->account_id . ' AND game_id=' . $player->game_id . ' GROUP BY message_type_id');

	if($db->nf()) {
		$messages = array();
		while($db->next_record()) {
			$messages[$db->f('message_type_id')] = $db->f('COUNT(*)');
		}

		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'message_view.php';

		if(isset($messages[MSG_GLOBAL])) {
			$container['folder_id'] = MSG_GLOBAL;
			print_link($container, '<img src="images/global_msg.png" border="0" alt="Global Messages">');
			echo '<small>' . $messages[MSG_GLOBAL] . '</small>';
		}

		if(isset($messages[MSG_PLAYER])) {
			$container['folder_id'] = MSG_PLAYER;
			print_link($container, '<img src="images/personal_msg.png" border="0" alt="Personal Messages">');
			echo '<small>' . $messages[MSG_PLAYER] . '</small>';
		}

		if(isset($messages[MSG_SCOUT])) {
			$container['folder_id'] = MSG_SCOUT;
			print_link($container, '<img src="images/scout_msg.png" border="0" alt="Scout Messages">');
			echo '<small>' . $messages[MSG_SCOUT] . '</small>';
		}

		if(isset($messages[MSG_POLITICAL])) {
			$container['folder_id'] = MSG_POLITICAL;
			print_link($container, '<img src="images/council_msg.png" border="0" alt="Political Messages">');
			echo '<small>' . $messages[MSG_POLITICAL] . '</small>';
		}

		if(isset($messages[MSG_ALLIANCE])) {
			$container['folder_id'] = MSG_ALLIANCE;
			print_link($container, '<img src="images/alliance_msg.png" border="0" alt="Alliance Messages">');
			echo '<small>' . $messages[MSG_ALLIANCE] . '</small>';
		}

		if(isset($messages[MSG_ADMIN])) {
			$container['folder_id'] = MSG_ADMIN;
			print_link($container, '<img src="images/admin_msg.png" border="0" alt="Admin Messages">');
			echo '<small>' . $messages[MSG_ADMIN] . '</small>';
		}

		if(isset($messages[MSG_PLANET])) {
			$container = array();
			$container['url'] = 'planet_msg_processing.php';
			print_link($container, '<img src="images/planet_msg.png" border="0" alt="Planet Messages">');
			echo '<small>' . $messages[MSG_PLANET] . '</small>';
		}
		echo '<br>';
	}

	echo $player->level_name . '<br><big>';

	$container = array();
	$container["url"]		= 'skeleton.php';
	$container["body"]		= 'trader_search_result.php';
	$container["player_id"]	= $player->player_id;
	print_link($container, $player->get_colored_name());
	echo '</big>';
	if (in_array($player->account_id, $HIDDEN_PLAYERS)) print("<br /><span style=\"font-variant:small-caps;color:red;\"><small>INVISIBLE</small></span>");
	echo '<br><br>Race : ' . $player->race_name;
	echo '<br>Turns : ' . $player->turns . '<br>';

	if ($player->newbie_turns > 0) {
		echo 'Newbie Turns Left: <span style="color:#';

		if ($player->newbie_turns > 20)
			echo '00BB00';
		else
			echo 'BB0000';

		echo ';">' . $player->newbie_turns  . '</span><br>';
	}

	echo 'Cash : ' . number_format($player->credits) . '<br>';
	echo 'Experience : ' . number_format($player->experience) . '<br>';
	echo 'Level : ' .  $player->level_id;
	echo '<br>Alignment : ' . get_colored_text($player->alignment,$player->alignment);
	echo '<br>Alliance : ' . $player->alliance_name;

	if ($player->alliance_id > 0) echo ' (' . $player->alliance_id . ')';
	echo '<br><br><b style="color:yellow;">' . $ship->ship_name . '</b><br>';

	$db->query("SELECT ship_name FROM ship_has_name WHERE game_id = $player->game_id AND " .
				"account_id = $player->account_id LIMIT 1");
	if ($db->next_record()) {
		//they have a name so we print it
		echo stripslashes($db->f("ship_name"));
	}

	echo 'Rating : ' . $ship->attack_rating() . '/' . $ship->defense_rating() . '<br>';

	// ******* Shields *******
	isset($ship->hardware[1]) ? $am=$ship->hardware[1] : $am=0;
	echo 'Shields : ';
	if ($under_attack_shields)
		echo '<span style="color:red;">' . $am . '</span>';
	else
		echo $am;
	echo '/' . $ship->max_hardware[HARDWARE_SHIELDS] . '<br>';

	// ******* Armor *******
	!empty($ship->hardware[2]) ? $am=$ship->hardware[2] : $am=0;
	echo 'Armor : ';
	if ($under_attack_armor)
		echo '<span style="color:red;">' . $am . '</span>';
	else
		echo $am;
	echo '/' . $ship->max_hardware[HARDWARE_ARMOR] . '<br>';

	// ******* Hardware *******
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'configure_hardware.php';

	print_link($container, '<strong>CIJSD</strong>');
	echo ' : ';
	!empty($ship->hardware[8]) ? $cijsd = '*' : $cijsd = '-';
	!empty($ship->hardware[9]) ? $cijsd .= '*' : $cijsd .= '-';
	!empty($ship->hardware[10]) ? $cijsd .= '*' : $cijsd .= '-';
	!empty($ship->hardware[7]) ? $cijsd .= '*' : $cijsd .= '-';
	!empty($ship->hardware[11]) ? $cijsd .= '*' : $cijsd .= '-';
	echo $cijsd;
	echo '<br /><br />';

	if ($ship->cloak_active()) echo '<strong style="color:lime;">*** Cloak active ***</strong><br /><br />';
	else if (!empty($ship->hardware[8])) echo '<strong style="color:red;">*** Cloak inactive ***</strong><br /><br />';


	if ($ship->get_illusion() > 0) {

		$db->query('SELECT ship_name FROM ship_type WHERE ship_type_id = ' . $ship->get_illusion() . ' LIMIT 1');
		$db->next_record();
		$ship_name = $db->f('ship_name');
		echo '<strong style="color:cyan;"> ' . $ship_name . '</strong><br />IG Rating : (' . $ship->get_illusion_attack() . '/' . $ship->get_illusion_defense() . ')<br /><br />';

	}

	// ******* Forces *******
	print_link(create_container('skeleton.php', 'forces_drop.php'), '<b>Forces</b>');
	echo '<br>';

	if (!empty($ship->hardware[HARDWARE_MINE])) {

		$container = array();
		$container['url'] = 'forces_drop_processing.php';
		$container['owner_id'] = $player->account_id;
		$container['drop_mines'] = 1;
		print_link($container, '<b>[x]</b> ');
	
	}
	echo 'Mines : ' . $ship->hardware[HARDWARE_MINE] . '/' . $ship->max_hardware[HARDWARE_MINE] . '<br>';
;
	if (!empty($ship->hardware[HARDWARE_COMBAT])) {

		$container = array();
		$container['url'] = 'forces_drop_processing.php';
		$container['owner_id'] = $player->account_id;
		$container['drop_combat_drones'] = 1;
		print_link($container, '<b>[x]</b> ');

	}
	echo 'Combat : ' . $ship->hardware[HARDWARE_COMBAT] . '/' . $ship->max_hardware[HARDWARE_COMBAT] . '<br>';

	if (!empty($ship->hardware[HARDWARE_SCOUT]))  {

		$container = array();
		$container['url'] = 'forces_drop_processing.php';
		$container['owner_id'] = $player->account_id;
		$container['drop_scout_drones'] = 1;
		print_link($container, '<b>[x]</b> ');

	}
	echo 'Scout : ' . $ship->hardware[HARDWARE_SCOUT] . '/' . $ship->max_hardware[HARDWARE_SCOUT];
	echo '<br><br>';
	print_link(create_container('skeleton.php', 'cargo_dump.php'), '<b>Cargo Holds</b>');

	echo '&nbsp;(' . $ship->hardware[3] . '/' . $ship->max_hardware[3] . ')<br>';


	foreach ($ship->cargo as $id => $amount)
		if ($amount > 0) {

			$db->query('SELECT good_name FROM good WHERE good_id=' .  $id);
			if ($db->next_record())
				echo '<img src="images/port/' . $id . '.png" alt="' . $db->f("good_name") . '">&nbsp;:&nbsp;' . $amount . '<br>';

		}

	echo 'Empty : ' . $ship->cargo_left;
	echo '<br><br>';
	print_link(create_container('skeleton.php', 'weapon_reorder.php'), '<b>Weapons</b>');
	echo '<br>';

	foreach($ship->weapon as $weapon_name)
		echo $weapon_name . '<br>';

	echo 'Open : ' . $ship->weapon_open;
	
}

echo '
	</div></div>
	</td>
</tr>
<tr>
<td class="footer_left">
<div style="width:294px;text-align:center">Get <b><u>FREE TURNS</u></b> for voting if you see the star.</div>
';

$container=array();
$container["url"] = "vote_link.php";

$in_game = isset(SmrSession::$game_id) && SmrSession::$game_id>0;
if($in_game) {

	$db->query('SELECT link_id,timeout FROM vote_links WHERE account_id=' . SmrSession::$old_account_id . ' ORDER BY link_id LIMIT 3');
	while($db->next_record()){
		if($db->f('timeout') < time() - 86400) {
			$turns_for_votes[$db->f('link_id')] = 1;
		}
		else {
			$turns_for_votes[$db->f('link_id')] = 0;
		}
	}
}

$vote_links = array();
$vote_links[1] = array('default_img' => 'mpogd.png', 'star_img' => 'mpogd_vote.png', 'location' => 'http://www.mpogd.com/gotm/vote.asp', 'name' => 'MPOGD');
$vote_links[2] = array('default_img' => 'omgn.jpg', 'star_img' => 'omgn_vote.jpg', 'location' => 'http://www.omgn.com/topgames/vote.php?Game_ID=30', 'name' => 'OMGN');
$vote_links[3] = array('default_img' => 'twg.png', 'star_img' => 'twg_vote.png', 'location' => 'http://www.topwebgames.com/in.asp?id=136', 'name' => 'TWG');

for($i=1;$i<4;$i++){
	echo '<a href=';
	if($in_game && (!isset($turns_for_votes[$i]) || ($turns_for_votes[$i] && rand(0,100) < 80))) {

		$container['link_id'] = $i;
		echo '\'javascript:voteSite("' . $vote_links[$i]['location'] . '",';
		echo '"' . SmrSession::get_new_href($container) . '")\'';
		$img = $vote_links[$i]['star_img'];
	}
	else {
		echo '"' . $vote_links[$i]['location'] . '" target="_blank"';
		$img = $vote_links[$i]['default_img'];
	}

	echo '><img class="vote_site" src="images/game_sites/' . $img . '" alt="' . $vote_links[$i]['name'] . '"></a>';
}

echo '
</td>
<td class="footer_right">
';

$db->query('SELECT * FROM version ORDER BY went_live DESC LIMIT 1');

$version = 0;
if ($db->next_record()) {

	$version_id = $db->f('version_id');
	$container = array('url' => 'skeleton.php', 'body' => 'changelog_view.php', 'version_id' => $version_id );
	$version = create_link($container, 'v' . $db->f('major_version') . '.' . $db->f('minor_version') . '.' . $db->f('patch_level'));

}

$time_elapsed = getmicrotime() - $time_start;
echo 'Space Merchant Realms<br />' . $version . ' &copy; 2001-' .  date("Y") . '<br />Hosted by <a href="http://www.fem.tu-ilmenau.de/index.php?id=93&L=1" target="fem">FeM</a><br />Script&nbsp;runtime: ' . number_format($time_elapsed, 3) . ' sec';
$launch = mktime(0,0,0,3,12,2008);
$now = time();
if ($launch - $now > 0)
	echo '<br />SMR 1.5 Launch in ' . format_time($launch - $now, TRUE) . '</span>!';
echo '
</td>
</tr>
</table>';
//var_dump($GLOBALS);
echo '
</body>
</html>
';

SmrSession::update();

?>
