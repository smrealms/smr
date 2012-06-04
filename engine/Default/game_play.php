<?
require_once(get_file_loc('SmrHistoryMySqlDatabase.class.inc'));
if(isset($var['errorMsg']))
	$PHP_OUTPUT.=($var['errorMsg'].'<br />');
if (isset($var['msg']))
	$PHP_OUTPUT.=($var['msg'].'<br />');

$template->assign('UserRankingLink',SmrSession::get_new_href(create_container('skeleton.php', 'rankings_view.php')));
$template->assign('UserRankName',$account->get_rank_name());

$db->query('SELECT end_date, game.game_id as game_id, game_name, game_speed,game_type FROM game, player ' .
					'WHERE game.game_id = player.game_id AND ' .
						  'account_id = '.SmrSession::$account_id.' AND ' .
						  'end_date >= \'' . TIME . '\'');
$games = array();
$games['Play'] = array();
$game_id_list ='';
if ($db->getNumRows() > 0)
{
	while ($db->nextRecord()) {

		$game_id = $db->getField('game_id');
		$games['Play'][$game_id]['ID'] = $game_id;
		$games['Play'][$game_id]['Name'] = $db->getField('game_name');
		$games['Play'][$game_id]['Type'] = $db->getField('game_type');
		$games['Play'][$game_id]['EndDate'] = date(DATE_DATE_SHORT,$db->getField('end_date'));
		$games['Play'][$game_id]['Speed'] = $db->getField('game_speed');
		$games['Play'][$game_id]['Type'] = $db->getField('game_type');
		
		$container = array();
		$container['game_id'] = $game_id;
		$container['url'] = 'game_play_processing.php';
		if($games['Play'][$game_id]['Type'] == 'Classic')
			$games['Play'][$game_id]['PlayGameLink'] = 'loader2.php?sn=' . SmrSession::addLink($container);
		else
			$games['Play'][$game_id]['PlayGameLink'] = SmrSession::get_new_href($container);

		// creates a new player object
		$curr_player =& SmrPlayer::getPlayer(SmrSession::$account_id, $game_id);
		$curr_ship =& $curr_player->getShip();

		// update turns for this game
		$curr_player->updateTurns();

		// generate list of game_id that this player is joined
		if (strlen($game_id_list)>0) $game_id_list .= ',';
		$game_id_list .= $game_id;

		$db2 = new SmrMySqlDatabase();
		$db2->query('SELECT count(*) as num_playing FROM player ' .
					'WHERE last_cpl_action >= ' . (TIME - 600) . ' AND ' .
						  'game_id = '.$game_id);
		$db2->nextRecord();
		$games['Play'][$game_id]['NumberPlaying'] = $db2->getField('num_playing');

		// create a container that will hold next url and additional variables.

		$container_game = array();
		$container_game['url'] = 'skeleton.php';
		$container_game['body'] = 'game_stats.php';
		$container_game['game_id'] = $game_id;
		$games['Play'][$game_id]['GameStatsLink'] = SmrSession::get_new_href($container_game);
		$games['Play'][$game_id]['Maintenance'] = $curr_player->getTurns();
		$games['Play'][$game_id]['LastActive'] = format_time(TIME-$curr_player->getLastCPLAction(),TRUE);
		$games['Play'][$game_id]['LastMovement'] = format_time(TIME-$curr_player->getLastActive(),TRUE);

	}
}

// put parenthesis around the list
$game_id_list = '('.$game_id_list.')';

if ($game_id_list == '()')
	$db->query('SELECT start_date, end_date, game.game_id as game_id, game_name, max_players, game_type, credits_needed, game_speed ' .
					'FROM game WHERE end_date >= \'' . TIME . '\' AND enabled = \'TRUE\'');
else
	$db->query('SELECT start_date, end_date, game.game_id as game_id, game_name, max_players, game_type, credits_needed, game_speed ' .
					'FROM game WHERE game_id NOT IN '.$game_id_list.' AND ' .
									'end_date >= \'' . TIME . '\' AND enabled = \'TRUE\'');

// ***************************************
// ** Join Games
// ***************************************

// are there any results?
if ($db->getNumRows() > 0)
{
	$games['Join'] = array();
	// iterate over the resultset
	while ($db->nextRecord())
	{
		$game_id = $db->getField('game_id');
		$games['Join'][$game_id]['ID'] = $game_id;
		$games['Join'][$game_id]['Name'] = $db->getField('game_name');
		$games['Join'][$game_id]['StartDate'] = date(DATE_DATE_SHORT,$db->getField('start_date'));
		$games['Join'][$game_id]['EndDate'] = date(DATE_DATE_SHORT,$db->getField('end_date'));
		$games['Join'][$game_id]['MaxPlayers'] = $db->getField('max_players');
		$games['Join'][$game_id]['Type'] = $db->getField('game_type');
		$games['Join'][$game_id]['Speed'] = $db->getField('game_speed');
		$games['Join'][$game_id]['Credits'] = $db->getField('credits_needed');
		// create a container that will hold next url and additional variables.
		$container = array();
		$container['game_id'] = $game_id;
		$container['url'] = 'skeleton.php';
		$container['body'] = 'game_join.php';

		$games['Join'][$game_id]['JoinGameLink'] = SmrSession::get_new_href($container);
	}
}
	
// ***************************************
// ** Previous Games
// ***************************************

$historyDB = new SmrHistoryMySqlDatabase();
$historyDB->query('SELECT start_date, ' .
		   'end_date, game_name, speed, game_id ' .
		   'FROM game ORDER BY game_id');
if ($historyDB->getNumRows())
{
	$games['Previous'] = array();
	while ($historyDB->nextRecord())
	{
		$game_id = $historyDB->getField('game_id');
		$games['Previous'][$game_id]['ID'] = $game_id;
		$games['Previous'][$game_id]['Name'] = $historyDB->getField('game_name');
		$games['Previous'][$game_id]['StartDate'] = date(DATE_DATE_SHORT,$historyDB->getField('start_date'));
		$games['Previous'][$game_id]['EndDate'] = date(DATE_DATE_SHORT,$historyDB->getField('end_date'));
		$games['Previous'][$game_id]['Speed'] = $historyDB->getField('speed');
		// create a container that will hold next url and additional variables.
		$container = array();
		$container['game_id'] = $game_id;
		$container['url'] = 'skeleton.php';
		$container['game_id'] = $game_id;
		$container['game_name'] = $games['Previous'][$game_id]['Name'];
		$container['body'] = 'games_previous.php';

		$games['Previous'][$game_id]['PreviousGameLink'] = SmrSession::get_new_href($container);
		$container['body'] = 'hall_of_fame_new.php';
		$games['Previous'][$game_id]['PreviousGameHOFLink'] = SmrSession::get_new_href($container);
		$container['body'] = 'games_previous_news.php';
		$games['Previous'][$game_id]['PreviousGameNewsLink'] = SmrSession::get_new_href($container);
		$container['body'] = 'games_previous_detail.php';
		$games['Previous'][$game_id]['PreviousGameStatsLink'] = SmrSession::get_new_href($container);
	}
}

$db = new SmrMySqlDatabase(); // restore database

$template->assign('Games',$games);

// ***************************************
// ** Donation Link
// ***************************************

$template->assign('DonateLink', SmrSession::get_new_href(create_container('skeleton.php', 'donation.php')));

// ***************************************
// ** Announcements View
// ***************************************
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'announcements.php';
$container['view_all'] = 'yes';
$template->assign('OldAnnouncementsLink',SmrSession::get_new_href($container));

// ***************************************
// ** Admin Functions
// ***************************************
$db->query('SELECT * FROM account_has_permission NATURAL JOIN permission WHERE account_id = '.$account->account_id);

if ($db->getNumRows())
{
	$adminPermissions = array();
	while ($db->nextRecord())
	{
		$adminPermissions[] = array( 'PermissionLink' => $db->getField('link_to')?SmrSession::get_new_href(create_container('skeleton.php',$db->getField('link_to'))):false,
					'Name' => $db->getField('permission_name'));
	}
	$template->assign('AdminPermissions',$adminPermissions);
}

?>
