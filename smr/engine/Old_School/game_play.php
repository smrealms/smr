<?
require_once(get_file_loc('smr_history_db.inc'));
if (isset($var['msg']))
	$PHP_OUTPUT.=($var['msg'].'<br />');

$smarty->assign('UserRankingLink',SmrSession::get_new_href(create_container('skeleton.php', 'rankings_view.php')));
$smarty->assign('UserRankName',$account->get_rank_name());

$db->query('SELECT DATE_FORMAT(end_date, \'%c/%e/%Y\') as format_end_date, end_date, game.game_id as game_id, game_name, game_speed FROM game, player ' .
					'WHERE game.game_id = player.game_id AND ' .
						  'account_id = '.SmrSession::$account_id.' AND ' .
						  'end_date >= \'' . date('Y-m-d') . '\'');
$games = array();
$games['Play'] = array();
$game_id_list ='';
if ($db->nf() > 0)
{
	while ($db->next_record()) {

		$game_id = $db->f('game_id');
		$game_name = $db->f('game_name');
		$end_date = $db->f('format_end_date');
		$game_speed = $db->f('game_speed');
		$games['Play'][$game_id]['ID'] = $game_id;
		$games['Play'][$game_id]['Name'] = $game_name;
		$games['Play'][$game_id]['EndDate'] = $end_date;
		$games['Play'][$game_id]['Speed'] = $game_speed;	
		
		$container = array();
		$container['game_id'] = $game_id;
		$container['url'] = 'game_play_processing.php';
		$games['Play'][$game_id]['PlayGameLink'] = SmrSession::get_new_href($container);

		// creates a new player object
//		$curr_player =& SmrPlayer::getPlayer(SmrSession::$account_id, $game_id);
		//PAGE
		$curr_player =& SmrPlayer::getPlayer(SmrSession::$account_id, $game_id);
		$curr_ship =& SmrShip::getShip($game_id,SmrSession::$account_id);

		// update turns for this game
		$curr_player->updateTurns();

		// generate list of game_id that this player is joined
		if (strlen($game_id_list)>0) $game_id_list .= ',';
		$game_id_list .= $game_id;

		$db2 = new SMR_DB();
		$db2->query('SELECT count(*) as num_playing FROM player ' .
					'WHERE last_cpl_action >= ' . (time() - 600) . ' AND ' .
						  'game_id = '.$game_id);
		$db2->next_record();
		$games['Play'][$game_id]['NumberPlaying'] = $db2->f('num_playing');

		// create a container that will hold next url and additional variables.

		$container_game = array();
		$container_game['url'] = 'skeleton.php';
		$container_game['body'] = 'game_stats.php';
		$container_game['game_id'] = $game_id;
		$games['Play'][$game_id]['GameStatsLink'] = SmrSession::get_new_href($container_game);
		$games['Play'][$game_id]['Maintenance'] = $curr_player->getTurns();
		$games['Play'][$game_id]['LastActive'] = format_time($TIME-$curr_player->getLastCPLAction(),TRUE);
		$games['Play'][$game_id]['LastMovement'] = format_time($TIME-$curr_player->getLastActive(),TRUE);

	}
}

// put parenthesis around the list
$game_id_list = '('.$game_id_list.')';

if ($game_id_list == '()')
	$db->query('SELECT DATE_FORMAT(start_date, \'%c/%e/%Y\') as start_date, DATE_FORMAT(end_date, \'%c/%e/%Y\') as end_date, game.game_id as game_id, game_name, max_players, game_type, credits_needed, game_speed ' .
					'FROM game WHERE end_date >= \'' . date('Y-m-d') . '\' AND enabled = \'TRUE\'');
else
	$db->query('SELECT DATE_FORMAT(start_date, \'%c/%e/%Y\') as start_date, DATE_FORMAT(end_date, \'%c/%e/%Y\') as end_date, game.game_id as game_id, game_name, max_players, game_type, credits_needed, game_speed ' .
					'FROM game WHERE game_id NOT IN '.$game_id_list.' AND ' .
									'end_date >= \'' . date('Y-m-d') . '\' AND enabled = \'TRUE\'');

// ***************************************
// ** Join Games
// ***************************************

// are there any results?
if ($db->nf() > 0)
{
	$games['Join'] = array();
	// iterate over the resultset
	while ($db->next_record())
	{
		$game_id = $db->f('game_id');
		$games['Join'][$game_id]['ID'] = $game_id;
		$games['Join'][$game_id]['Name'] = $db->f('game_name');
		$games['Join'][$game_id]['StartDate'] = $db->f('start_date');
		$games['Join'][$game_id]['EndDate'] = $db->f('end_date');
		$games['Join'][$game_id]['MaxPlayers'] = $db->f('max_players');
		$games['Join'][$game_id]['Type'] = $db->f('max_players');
		$games['Join'][$game_id]['Speed'] = $db->f('credits_needed');
		$games['Join'][$game_id]['Credits'] = $db->f('credits_needed');
		// create a container that will hold next url and additional variables.
		$container = array();
		$container['game_id'] = $game_id;
		$container['url'] = 'skeleton.php';
		$container['body'] = 'game_join.php';

		$games['Join'][$game_id]['JoinGameLink'] = SmrSession::get_new_href($container);
	}
}

$smarty->assign('Games',$games);
	
// ***************************************
// ** Previous Games
// ***************************************

$db = new SMR_HISTORY_DB();
$db->query('SELECT DATE_FORMAT(start_date, \'%c/%e/%Y\') as start_date, ' .
		   'DATE_FORMAT(end_date, \'%c/%e/%Y\') as end_date, game_name, speed, game_id ' .
		   'FROM game ORDER BY game_id');
if ($db->nf())
{

	$PHP_OUTPUT.=('<p>');
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr><th align=center>Game Name</th><th align=center>Start Date</th><th align=center>End Date</th><th align=center>Speed</th><th align=center colspan=3>Options</th></tr>');
	while ($db->next_record()) {

		$id = $db->f('game_id');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['game_id'] = $db->f('game_id');
		$container['game_name'] = $db->f('game_name');
		$container['body'] = 'games_previous.php';
		$name = $db->f('game_name');
		$PHP_OUTPUT.=('<tr><td>');
		$PHP_OUTPUT.=create_link($container, '.$db->escapeString($name ($id)');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td align=center>' . $db->f('start_date') . '</td>');
		$PHP_OUTPUT.=('<td align=center>' . $db->f('end_date') . '</td>');
		$PHP_OUTPUT.=('<td align=center>' . $db->f('speed') . '</td>');
		$PHP_OUTPUT.=('<td align=center>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'hall_of_fame_new.php';
		$container['game_id'] = $db->f('game_id');
		$PHP_OUTPUT.=create_link($container, 'Hall of Fame');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td align=center>');
		$container['body'] = 'games_previous_news.php';
		$container['game_id'] = $db->f('game_id');
		$container['game_name'] = $db->f('game_name');
		$PHP_OUTPUT.=create_link($container, 'Game News');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td align=center>');
		$container['body'] = 'games_previous_detail.php';
		$container['game_id'] = $db->f('game_id');
		$container['game_name'] = $db->f('game_name');
		$PHP_OUTPUT.=create_link($container, 'Game Stats');
		$PHP_OUTPUT.=('</td>');

	}

	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('</p>');

}

// restore old database
$db = new SMR_DB();


// ***************************************
// ** Donation Link
// ***************************************

$smarty->assign('DonateLink', SmrSession::get_new_href(create_container('skeleton.php', 'donation.php')));

// ***************************************
// ** Announcements View
// ***************************************
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'announcements.php';
$container['view_all'] = 'yes';
$smarty->assign('OldAnnouncementsLink',SmrSession::get_new_href($container));

// ***************************************
// ** Admin Functions
// ***************************************
$db->query('SELECT * FROM account_has_permission NATURAL JOIN permission WHERE account_id = '.$account->account_id);

if ($db->nf())
{
	$adminPermissions = array();
	while ($db->next_record())
	{
		$adminPermissions[] = array( 'PermissionLink' => SmrSession::get_new_href(create_container('skeleton.php',$db->f('link_to'))), 'Name' => $db->f('permission_name'));
	}
	$smarty->assign('AdminPermissions',$adminPermissions);
}

?>
