<?php
echo '<span style="color:yellow">' . date('n/j/Y\<b\r /\>g:i:s A') . '</span><br><br>';

$container = array();
$container['url'] = 'skeleton.php';

if ($session->game_id > 0) {
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
	echo '<a href="' . $URL . '/map_galaxy.php" target="_blank">Galaxy Map</a><br><br>';
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

if ($session->account_id > 0 && empty($var['logoff'])) {
	$container['body'] = '';
	$container['url'] = 'game_play_preprocessing.php';
	print_link($container, 'Play Game');
	echo '<br>';
	$container['url'] = 'logoff_preprocessing.php';
	print_link($container, 'Logoff');
}
else {
	echo '<a href="login.php">Login</a><br>';
}

echo '<br><br>';
echo '<a href="' . $URL . '/manual.php" target="_blank">Manual</a><br>';
$container['url'] = 'skeleton.php';
$container['body'] = 'preferences.php';
print_link($container, 'Preferences');
echo '<br>';
$container['body'] = '';
$container['url'] = 'mgu_create.php';
print_link($container, 'DL MGU Maps');
echo '<br>';
$container['body'] = '';
$container['url'] = 'mgu_create_new.php';
print_link($container, 'MGU Test');
echo '<br>';
$container['url'] = 'skeleton.php';
$container['body'] = 'album_edit.php';
print_link($container, 'Edit Photo');
echo '<br>';
echo '<a href="' . $URL . '/album/" target="_blank">Album</a><br><br>';

$container['body'] = 'bug_report.php';
print_link($container, 'Report a Bug');
echo '<br>';
$container['body'] = 'contact.php';
print_link($container, 'Contact Form');
echo '<br><br><b>';

if ($session->game_id > 0) {
	echo '<big>';
	$container['body'] = 'chat_rules.php';
	print_link($container, 'IRC Chat');
	echo '</big><br>';
}

echo '<a href="http://smrcnn.smrealms.de/viewtopic.php?t=3515/album/" target="_blank">User Policy</a><br>';
echo '<a href="http://smrcnn.smrealms.de/" target="_blank">WebBoard</a></b><br>';
$container['body'] = 'donation.php';
print_link($container, 'Donate');

?>