<?
$PHP_OUTPUT.= '<span style="color:yellow">' . date('n/j/Y\<b\r /\>g:i:s A') . '</span><br /><br />';

$container = array();
$container['url'] = 'skeleton.php';

if (SmrSession::$game_id > 0) {
	$PHP_OUTPUT.= '<big><b>';
	if (!$player->isLandedOnPlanet()) {
		$container['body'] = 'current_sector.php';
		$PHP_OUTPUT.=create_link($container, 'Current Sector');
		$PHP_OUTPUT.= '<br />';
		$container['body'] = 'map_local.php';
		$PHP_OUTPUT.=create_link($container, 'Local Map');
	}
	else {
		$container['url'] = 'planet_main_processing.php';
		$container['body'] = '';
		$PHP_OUTPUT.=create_link($container, 'Planet Main');
	}
	$PHP_OUTPUT.= '<br />';
	$container['url'] = 'skeleton.php';
	$container['body'] = 'course_plot.php';
	$PHP_OUTPUT.=create_link($container, 'Plot a Course');
	$PHP_OUTPUT.= '</b></big><br />';
	$PHP_OUTPUT.= '<a href="' . URL . '/map_galaxy.php" target="_blank">Galaxy Map</a><br /><br />';
	$container['url'] = 'skeleton.php';
	$container['body'] = 'trader_status.php';
	$PHP_OUTPUT.=create_link($container, 'Trader');
	$PHP_OUTPUT.= '<br />';
	if ($player->getAllianceID() > 0) {
		$container['body'] = 'alliance_mod.php';
	} else {
		$container['body'] = 'alliance_list.php';
		$container['order'] = 'alliance_name';
	}
	$PHP_OUTPUT.=create_link($container, 'Alliance');
	$PHP_OUTPUT.= '<br />';
	$container['body'] = 'combat_log_viewer.php';
	$PHP_OUTPUT.=create_link($container, 'Combat Logs');
	$PHP_OUTPUT.= '<br /><br />';
	$container['body'] = 'trader_planet.php';
	$PHP_OUTPUT.=create_link($container, 'Planet');
	$PHP_OUTPUT.= '<br />';
	$container['body'] = 'forces_list.php';
	$PHP_OUTPUT.=create_link($container, 'Forces');
	$PHP_OUTPUT.= '<br /><br />';
	$container['body'] = 'message_view.php';
	$PHP_OUTPUT.=create_link($container, 'Messages');
	$PHP_OUTPUT.= '<br />';
	$container['body'] = 'news_read_current.php';
	$PHP_OUTPUT.=create_link($container, 'Read News');
	$PHP_OUTPUT.= '<br />';
	$container['body'] = 'galactic_post_read.php';
	$PHP_OUTPUT.=create_link($container, 'Galactic Post');
	$PHP_OUTPUT.= '<br /><br />';
	$container['body'] = 'trader_search.php';
	$PHP_OUTPUT.=create_link($container, 'Search for Trader');
	$PHP_OUTPUT.= '<br />';
	$container['body'] = 'current_players.php';
	$PHP_OUTPUT.=create_link($container, 'Current Players');
	$PHP_OUTPUT.= '<br /><br />';
	$container['body'] = 'rankings_player_experience.php';
	$PHP_OUTPUT.=create_link($container, 'Rankings');
	$PHP_OUTPUT.= '<br />';
	$container['body'] = 'hall_of_fame_new.php';
	$PHP_OUTPUT.=create_link($container, 'Hall of Fame');
	$PHP_OUTPUT.= '<br />';
	$container['body'] = 'hall_of_fame_new.php';
	$container['game_id'] = $player->getGameID();
	$PHP_OUTPUT.=create_link($container, 'Current HoF');
	unset($container['game_id']);
	$PHP_OUTPUT.= '<br /><br />';
}

if (SmrSession::$account_id > 0 && empty($var['logoff'])) {
	$container['body'] = '';
	$container['url'] = 'game_play_preprocessing.php';
	$PHP_OUTPUT.=create_link($container, 'Play Game');
	$PHP_OUTPUT.= '<br />';
	$container['url'] = 'logoff_preprocessing.php';
	$PHP_OUTPUT.=create_link($container, 'Logoff');
}
else {
	$PHP_OUTPUT.= '<a href="login.php">Login</a><br />';
}

$PHP_OUTPUT.= '<br /><br />';
$PHP_OUTPUT.= '<a href="' . URL . '/manual.php" target="_blank">Manual</a><br />';
$container['url'] = 'skeleton.php';
$container['body'] = 'preferences.php';
$PHP_OUTPUT.=create_link($container, 'Preferences');
$PHP_OUTPUT.= '<br />';
$container['body'] = '';
$container['url'] = 'mgu_create.php';
$PHP_OUTPUT.=create_link($container, 'DL MGU Maps');
$PHP_OUTPUT.= '<br />';
$container['body'] = '';
$container['url'] = 'mgu_create_new.php';
$PHP_OUTPUT.=create_link($container, 'MGU Test');
$PHP_OUTPUT.= '<br />';
$container['url'] = 'skeleton.php';
$container['body'] = 'album_edit.php';
$PHP_OUTPUT.=create_link($container, 'Edit Photo');
$PHP_OUTPUT.= '<br />';
$PHP_OUTPUT.= '<a href="' . URL . '/album/" target="_blank">Album</a><br /><br />';

$container['body'] = 'bug_report.php';
$PHP_OUTPUT.=create_link($container, 'Report a Bug');
$PHP_OUTPUT.= '<br />';
$container['body'] = 'contact.php';
$PHP_OUTPUT.=create_link($container, 'Contact Form');
$PHP_OUTPUT.= '<br /><br /><b>';

if (SmrSession::$game_id > 0) {
	$PHP_OUTPUT.= '<big>';
	$container['body'] = 'chat_rules.php';
	$PHP_OUTPUT.=create_link($container, 'IRC Chat');
	$PHP_OUTPUT.= '</big><br />';
}

$PHP_OUTPUT.= '<a href="http://smrcnn.smrealms.de/viewtopic.php?t=3515/album/" target="_blank">User Policy</a><br />';
$PHP_OUTPUT.= '<a href="http://smrcnn.smrealms.de/" target="_blank">WebBoard</a></b><br />';
$container['body'] = 'donation.php';
$PHP_OUTPUT.=create_link($container, 'Donate');

?>