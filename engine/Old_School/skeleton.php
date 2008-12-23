<?

$smarty->assign('fontSize',$account->fontsize);
$smarty->assign('timeDisplay',date('n/j/Y\<b\r /\>g:i:s A',$TIME));

$container = array();
$container['url'] = 'skeleton.php';

if (SmrSession::$game_id > 0) {
	$smarty->assign('GameID',SmrSession::$game_id);

	if ($player->isLandedOnPlanet())
	{
		$container['url'] = 'planet_main_processing.php';
		$container['body'] = '';
		$smarty->assign('PlanetMainLink',SmrSession::get_new_href($container));
	}
	else
	{
		$smarty->assign('CurrentSectorLink',Globals::getCurrentSectorHREF());

		$container['body'] = 'map_local.php';
		$smarty->assign('LocalMapLink',SmrSession::get_new_href($container));
	}

	$container['url'] = 'skeleton.php';
	$container['body'] = 'course_plot.php';
	$smarty->assign('PlotCourseLink',SmrSession::get_new_href($container));
	
	$container['url'] = 'skeleton.php';
	$container['body'] = 'trader_status.php';
	$smarty->assign('TraderLink',SmrSession::get_new_href($container));

	if ($player->getAllianceID() > 0)
	{
		$container['body'] = 'alliance_mod.php';
	}
	else
	{
		$container['body'] = 'alliance_list.php';
		$container['order'] = 'alliance_name';
	}
	$smarty->assign('AllianceLink',SmrSession::get_new_href($container));

	$container['body'] = 'combat_log_viewer.php';
	$smarty->assign('CombatLogsLink',SmrSession::get_new_href($container));

	$container['body'] = 'trader_planet.php';
	$smarty->assign('PlanetLink',SmrSession::get_new_href($container));

	$container['body'] = 'forces_list.php';
	$smarty->assign('ForcesLink',SmrSession::get_new_href($container));

	$container['body'] = 'message_view.php';
	$smarty->assign('MessagesLink',SmrSession::get_new_href($container));

	$container['body'] = 'news_read_current.php';
	$smarty->assign('ReadNewsLink',SmrSession::get_new_href($container));

	$container['body'] = 'galactic_post_read.php';
	$smarty->assign('GalacticPostLink',SmrSession::get_new_href($container));

	$container['body'] = 'trader_search.php';
	$smarty->assign('SearchForTraderLink',SmrSession::get_new_href($container));

	$container['body'] = 'current_players.php';
	$smarty->assign('CurrentPlayersLink',SmrSession::get_new_href($container));

	$container['body'] = 'rankings_player_experience.php';
	$smarty->assign('RankingsLink',SmrSession::get_new_href($container));

	$container['body'] = 'hall_of_fame_new.php';
	$smarty->assign('HallOfFameLink',SmrSession::get_new_href($container));

	$container['body'] = 'hall_of_fame_new.php';
	$container['game_id'] = $player->getGameID();
	$smarty->assign('CurrentHallOfFameLink',SmrSession::get_new_href($container));
	unset($container['game_id']);
}

if (SmrSession::$account_id > 0 && empty($var['logoff']))
{
	$smarty->assign('AccountID',SmrSession::$account_id);
	$container['body'] = '';
	$container['url'] = 'game_play_preprocessing.php';
	$smarty->assign('PlayGameLink',SmrSession::get_new_href($container));

	$container['url'] = 'logoff_preprocessing.php';
	$smarty->assign('LogoutLink',SmrSession::get_new_href($container));
}

$container['url'] = 'skeleton.php';
$container['body'] = 'preferences.php';
$smarty->assign('PreferencesLink',SmrSession::get_new_href($container));

/*
$container['body'] = '';
$container['url'] = 'mgu_create.php';
$PHP_OUTPUT.=create_link($container, 'DL MGU Maps');
echo '<br>';
*/
//if (SmrSession::$game_id > 0) {
//	$container['body'] = '';
//	$container['url'] = 'mgu_create_new.php';
//	$PHP_OUTPUT.=create_link($container, 'DL MGU Maps');
//	echo '<br>';
//}
$container['url'] = 'skeleton.php';
$container['body'] = 'album_edit.php';
$smarty->assign('EditPhotoLink',SmrSession::get_new_href($container));

$container['body'] = 'bug_report.php';
$smarty->assign('ReportABugLink',SmrSession::get_new_href($container));

$container['body'] = 'contact.php';
$smarty->assign('ContactFormLink',SmrSession::get_new_href($container));

if (SmrSession::$game_id > 0)
{
	$container['body'] = 'chat_rules.php';
	$smarty->assign('IRCLink',SmrSession::get_new_href($container));
}

$container['body'] = 'donation.php';
$smarty->assign('DonateLink',SmrSession::get_new_href($container));
$PHP_OUTPUT='';

include_once(get_file_loc($var['body']));
$smarty->assign('PHP_OUTPUT',$PHP_OUTPUT);

if (SmrSession::$game_id != 0)
{
	$smarty->assign('CurrentSectorID',$player->getSectorID());
//	if ($under_attack_shields || $under_attack_armor || $under_attack_drones) {
//		echo '
//			<div id="attack_warning" class="attack_warning"><nobr>You are under attack!</nobr></div>
//			<script type="text/javascript">
//			SetBlink();
//			</script>
//			';
//		$ship->mark_seen();
//	}

	$db->query('SELECT message_type_id,COUNT(*) FROM player_has_unread_messages WHERE account_id=' . $player->getAccountID() . ' AND game_id=' . $player->getGameID() . ' GROUP BY message_type_id');

	if($db->nf()) {
		$messages = array();
		while($db->next_record()) {
			$messages[$db->f('message_type_id')] = $db->f('COUNT(*)');
		}

		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'message_view.php';

		if(isset($messages[$GLOBALMSG])) {
			$container['folder_id'] = $GLOBALMSG;
			$smarty->assign('MessageGlobalLink',SmrSession::get_new_href($container));
			$smarty->assign('MessageGlobalNum',$messages[$GLOBALMSG]);
		}

		if(isset($messages[$PLAYERMSG])) {
			$container['folder_id'] = $PLAYERMSG;
			$smarty->assign('MessagePlayerLink',SmrSession::get_new_href($container));
			$smarty->assign('MessagePersonalNum',$messages[$PLAYERMSG]);
		}

		if(isset($messages[$SCOUTMSG])) {
			$container['folder_id'] = $SCOUTMSG;
			$smarty->assign('MessageScoutLink',SmrSession::get_new_href($container));
			$smarty->assign('MessageScoutNum',$messages[$SCOUTMSG]);
		}

		if(isset($messages[$POLITICALMSG])) {
			$container['folder_id'] = $POLITICALMSG;
			$smarty->assign('MessagePoliticalLink',SmrSession::get_new_href($container));
			$smarty->assign('MessagePoliticalNum',$messages[$POLITICALMSG]);
		}

		if(isset($messages[$ALLIANCEMSG])) {
			$container['folder_id'] = $ALLIANCEMSG;
			$smarty->assign('MessageAllianceLink',SmrSession::get_new_href($container));
			$smarty->assign('MessageAllianceNum',$messages[$ALLIANCEMSG]);
		}

		if(isset($messages[$ADMINMSG])) {
			$container['folder_id'] = $ADMINMSG;
			$smarty->assign('MessageAdminLink',SmrSession::get_new_href($container));
			$smarty->assign('MessageAdminNum',$messages[$ADMINMSG]);
		}

		if(isset($messages[$PLANETMSG])) {
			$container = array();
			$container['url'] = 'planet_msg_processing.php';
			$smarty->assign('MessagePlanetLink',SmrSession::get_new_href($container));
			$smarty->assign('MessagePlanetNum',$messages[$PLANETMSG]);
		}
	}

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'trader_search_result.php';
	$container['player_id']	= $player->getPlayerID();
	$smarty->assign('PlayerNameLink',SmrSession::get_new_href($container));
	$smarty->assign('PlayerDisplayName',$player->getDisplayName());
	
	if (in_array($player->getAccountID(), $HIDDEN_PLAYERS)) $smarty->assign('PlayerInvisible',true);

	if ($player->getNewbieTurns() > NEWBIE_TURNS_WARNING_LIMIT)
		$colour = 'green';
	else
		$colour = 'red';
	$smarty->assign('NewbieTurnsColour',$colour);

	$db->query('SELECT ship_name FROM ship_has_name WHERE game_id = '.$player->getGameID().' AND ' .
				'account_id = '.$player->getAccountID().' LIMIT 1');
	if ($db->next_record()) {
		//they have a name so we echo it
		$smarty->assign('PlayerShipCustomName',stripslashes($db->f('ship_name')));
	}

	// ******* Hardware *******
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'configure_hardware.php';

	$smarty->assign('HardwareLink',SmrSession::get_new_href($container));

	// ******* Forces *******
	$smarty->assign('ForceDropLink',SmrSession::get_new_href(create_container('skeleton.php', 'forces_drop.php')));

	if ($ship->hasMines())
	{

		$container = array();
		$container['url'] = 'forces_drop_processing.php';
		$container['owner_id'] = $player->getAccountID();
		$container['drop_mines'] = 1;
		$smarty->assign('DropMineLink',SmrSession::get_new_href($container));
	}
	if ($ship->hasCDs()) 
	{

		$container = array();
		$container['url'] = 'forces_drop_processing.php';
		$container['owner_id'] = $player->getAccountID();
		$container['drop_combat_drones'] = 1;
		$smarty->assign('DropCDLink',SmrSession::get_new_href($container));

	}

	if ($ship->hasSDs())
	{

		$container = array();
		$container['url'] = 'forces_drop_processing.php';
		$container['owner_id'] = $player->getAccountID();
		$container['drop_scout_drones'] = 1;
		$smarty->assign('DropSDLink',SmrSession::get_new_href($container));

	}

	$smarty->assign('CargoJettisonLink',SmrSession::get_new_href(create_container('skeleton.php', 'cargo_dump.php')));

	$cargo = $ship->getCargo();
	foreach ($cargo as $id => $amount)
		if ($amount > 0) {

			$db->query('SELECT good_name FROM good WHERE good_id=' .  $id);
			if ($db->next_record())
				echo '<img src="images/port/' . $id . '.gif" alt="' . $db->f('good_name') . '">&nbsp;:&nbsp;' . $amount . '<br>';

		}

	$smarty->assign('WeaponReorderLink',SmrSession::get_new_href(create_container('skeleton.php', 'weapon_reorder.php')));

	
}

$container=array();
$container['url'] = 'vote_link.php';

$in_game = isset(SmrSession::$game_id) && SmrSession::$game_id>0;
if($in_game) {

	$db->query('SELECT link_id,timeout FROM vote_links WHERE account_id=' . SmrSession::$account_id . ' ORDER BY link_id LIMIT 3');
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
$vote_links[1] = array('default_img' => 'mpogd.gif', 'star_img' => 'mpogd_vote.gif', 'location' => 'http://www.mpogd.com/gotm/vote.asp', 'name' => 'MPOGD');
$vote_links[2] = array('default_img' => 'omgn.jpg', 'star_img' => 'omgn_vote.jpg', 'location' => 'http://www.omgn.com/topgames/vote.php?Game_ID=30', 'name' => 'OMGN');
$vote_links[3] = array('default_img' => 'twg.gif', 'star_img' => 'twg_vote.gif', 'location' => 'http://www.topwebgames.com/in.asp?id=136', 'name' => 'TWG');

$voteSite = array();
for($i=1;$i<4;$i++){
	$voteSite[$i] = '<a href=';
	if($in_game && (!isset($turns_for_votes[$i]) || ($turns_for_votes[$i] && rand(0,100) < 80))) {

		$container['link_id'] = $i;
		$voteSite[$i] .= '\'javascript:VoteSite("' . $vote_links[$i]['location'] . '",';
		$voteSite[$i] .= '"' . SmrSession::get_new_sn($container) . '")\'';
		$img = $vote_links[$i]['star_img'];
	}
	else {
		$voteSite[$i] .= '"' . $vote_links[$i]['location'] . '" target="_blank"';
		$img = $vote_links[$i]['default_img'];
	}

	$voteSite[$i] .= '><img class="vote_site" src="images/game_sites/' . $img . '" alt="' . $vote_links[$i]['name'] . '"></a>';
}
$smarty->assign('VoteSites',$voteSite);


$db->query('SELECT * FROM version ORDER BY went_live DESC LIMIT 1');
$version = '';
if ($db->next_record()) {

	$version_id = $db->f('version_id');
	$container = array('url' => 'skeleton.php', 'body' => 'changelog_view.php', 'version_id' => $version_id );
	$version = create_link($container, 'v' . $db->f('major_version') . '.' . $db->f('minor_version') . '.' . $db->f('patch_level'));

}

$smarty->assign('Version',$version);
$smarty->assign('CurrentYear',date('Y',$TIME));
$time_elapsed = microtime(true) - $time_start;
$smarty->assign('ScriptRuntime',number_format($time_elapsed,4));
$launch = mktime(0,0,0,3,12,2008);
$now = time();
if ($launch - $now > 0)
	echo '<br />SMR 1.5 Launch in ' . format_time($launch - $now, TRUE) . '</span>!';


SmrSession::update();

function format_time($seconds, $short=FALSE)
{
	$minutes=0;
	$hours=0;
	$days=0;
	$weeks=0;
	$string = '';
	if ($seconds == 0)
	{
		$string = '0 seconds';
		if ($short) $string = '0s';
	}
	if ($seconds >= 60)
	{
		$minutes = floor($seconds/60);
		$seconds = $seconds % 60;
	}
	if ($minutes >= 60)
	{
		$hours = floor($minutes/60);
		$minutes = $minutes % 60;
	}
	if ($hours >= 24)
	{
		$days = floor($hours/24);
		$hours = $hours % 24;
	}
	if ($days >= 7)
	{
		$weeks = floor($days/7);
		$days = $days % 7;
	}
	if ($weeks > 0)
	{
		$string .= $weeks;
		if ($short) $string .= 'w';
		else
		{
			$string .= ' week';
			if ($weeks > 1) $string .= 's';
		}
	}
	if ($days > 0)
	{
		$before = $weeks;
		$after = $hours + $minutes + $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $days;
		if ($short) $string .= 'd';
		else
		{
			$string .= ' day';
			if ($days > 1) $string .= 's';
		}
	}
	if ($hours > 0)
	{
		$before = $weeks + $days;
		$after = $minutes + $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $hours;
		if ($short) $string .= 'h';
		else
		{
			$string .= ' hour';
			if ($hours > 1) $string .= 's';
		}
	}
	if ($minutes > 0)
	{
		$before = $weeks + $days + $hours;
		$after = $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $minutes;
		if ($short) $string .= 'm';
		else
		{
			$string .= ' minute';
			if ($minutes > 1) $string .= 's';
		}
	}
	if ($seconds > 0)
	{
		$before = $weeks + $days + $hours + $minutes;
		$after = 0;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $seconds;
		if ($short) $string .= 's';
		else
		{
			$string .= ' second';
			if ($seconds > 1) $string .= 's';
		}
	}
	return $string;
}
?>
