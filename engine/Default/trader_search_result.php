<?php

$db2 = new SmrMySqlDatabase();

if(isset($_REQUEST['player_id']))
	SmrSession::updateVar('PlayerID',$_REQUEST['player_id']);
if (isset($var['player_id']))
	SmrSession::updateVar('PlayerID',$var['player_id']);
if (isset($var['PlayerID']))
	$player_id = $var['PlayerID'];
if(isset($_REQUEST['player_id']))
	SmrSession::updateVar('PlayerName',$_REQUEST['player_name']);
if (isset($var['PlayerName']))
	$player_name = $var['PlayerName'];
if (!is_numeric($player_id) && !empty($player_id))
	create_error('Please enter only numbers!');

$count = 0;
$template->assign('PageTopic','Search Trader Results');

if (!empty($player_id))
	$db->query('SELECT * FROM player ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
			   'player_id = '.$player_id.' LIMIT 5');
else
{
	if (empty($player_name))
		$player_name = '%';

	$db->query('SELECT * FROM player ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
					 'player_name = ' . $db->escapeString($player_name) . ' ' .
			   'ORDER BY player_name LIMIT 5');
}

if ($db->getNumRows() > 0)
{
	$PHP_OUTPUT.=('<table class="standard" width="75%">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Name</th>');
	$PHP_OUTPUT.=('<th>Alliance</th>');
	$PHP_OUTPUT.=('<th>Race</th>');
	$PHP_OUTPUT.=('<th>Experience</th>');
	$PHP_OUTPUT.=('<th>Online</th>');
	if (in_array($player->getAccountID(), $HIDDEN_PLAYERS)) $PHP_OUTPUT.=('<th>Sector</th>');
	$PHP_OUTPUT.=('<th>Option</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord())
	{
		$curr_player =& SmrPlayer::getPlayer($db->getField('account_id'), $player->getGameID());
		$PHP_OUTPUT.=('<tr>');

		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'trader_search_result.php';
		$container['player_id']	= $curr_player->getPlayerID();

		$PHP_OUTPUT.=('<td>');
		$PHP_OUTPUT.=create_link($container, $curr_player->getDisplayName());
		$PHP_OUTPUT.=('<br />');
		if ($curr_player->hasCustomShipName())
		{
			$PHP_OUTPUT.= $curr_player->getCustomShipName();
		}
		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('<td>');
		if ($curr_player->getAllianceID() > 0)
		{
			$container = array();
			$container['url']			= 'skeleton.php';
			$container['body']			= 'alliance_roster.php';
			$container['alliance_id']	= $curr_player->getAllianceID();
			$PHP_OUTPUT.=create_link($container, $curr_player->getAllianceName());
		}
		else
			$PHP_OUTPUT.=('(none)');
		$PHP_OUTPUT.=('</td>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'council_list.php';
		$container['race_id'] = $curr_player->getRaceID();
		$container['race_name'] = $curr_player->getRaceName();
		$PHP_OUTPUT.=('<td align="center" valign="middle">');
		$PHP_OUTPUT.=create_link($container, $player->getColouredRaceName($curr_player->getRaceID()));
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td align="center" valign="middle">'.$curr_player->getExperience().'</td>');
		if ($curr_player->getLastCPLAction() > TIME - 600)
			$PHP_OUTPUT.=('<td width="10%" align="center" valign="middle" class="dgreen">YES</td>');
		else
			$PHP_OUTPUT.=('<td width="10%" align="center" valign="middle" class="red">NO</td>');
		if (in_array($player->getAccountID(), $HIDDEN_PLAYERS)) $PHP_OUTPUT.=('<td align="center" valign="middle">'.$curr_player->getSectorID().'</td>');
		$PHP_OUTPUT.=('<td style="font-size:75%;" width="10%" class="center">');
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'message_send.php';
		$container['receiver']	= $curr_player->getAccountID();
		$PHP_OUTPUT.=create_link($container, '<span class="yellow">Send Message</span>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'bounty_view.php';
		$container['id'] = $curr_player->getAccountID();
		$PHP_OUTPUT.=create_link($container, '<br /><span class="yellow">View Bounty</span><br />');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'hall_of_fame_player_detail.php';
		$container['account_id'] = $curr_player->getAccountID();
		$container['game_id'] = $curr_player->getGameID();
		$container['sending_page'] = 'search';
		$PHP_OUTPUT.=create_link($container, '<span class="yellow">View Stats</span><br />');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'news_read_advanced.php';
		$container['submit'] = 'Search For Player';
		$container['playerName'] = $curr_player->getPlayerName();
		$PHP_OUTPUT.=create_link($container, '<span class="yellow">View News</span>');
		if (in_array($player->getAccountID(), $HIDDEN_PLAYERS))
		{
			$PHP_OUTPUT.= '<br />';
			$container=array();
			$container['url'] = 'sector_jump_processing.php';
			$container['to'] = $curr_player->getSectorID();
			$PHP_OUTPUT.=create_link($container, '<span class="yellow">Jump to Sector</span>');
		}
		$PHP_OUTPUT.=('</td></tr>');

	}
	$PHP_OUTPUT.=('</table>');
	$count++;
} 
if (empty($player_id))
{
	$real = $player_name;
	if (!empty($player_name))
		$player_name = '%' . $player_name . '%';
	else
		$player_name = '%';
	
	$db->query('SELECT * FROM player ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
					 'player_name LIKE ' . $db->escape_string($player_name, true) . ' AND player_name != ' . $db->escape_string($real, true) . ' ' .
			   'ORDER BY player_name LIMIT 5');
			   
	if ($db->getNumRows() > 0)
	{
		$PHP_OUTPUT.=('<table class="standard" width="75%">');
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<th>Name</th>');
		$PHP_OUTPUT.=('<th>Alliance</th>');
		$PHP_OUTPUT.=('<th>Race</th>');
		$PHP_OUTPUT.=('<th>Experience</th>');
		$PHP_OUTPUT.=('<th>Online</th>');
		if (in_array($player->getAccountID(), $HIDDEN_PLAYERS)) $PHP_OUTPUT.=('<th>Sector</th>');
		$PHP_OUTPUT.=('<th>Option</th>');
		$PHP_OUTPUT.=('</tr>');
	
		while ($db->nextRecord())
		{
			$curr_player =& SmrPlayer::getPlayer($db->getField('account_id'), $player->getGameID());

			$PHP_OUTPUT.=('<tr>');
	
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'trader_search_result.php';
			$container['player_id']	= $curr_player->getPlayerID();
	
			$PHP_OUTPUT.=('<td>');
			$PHP_OUTPUT.=create_link($container, $curr_player->getDisplayName());
			$PHP_OUTPUT.=('<br />');
			if ($curr_player->hasCustomShipName())
			{
				$PHP_OUTPUT.= $curr_player->getCustomShipName();
			}
			$PHP_OUTPUT.=('</td>');
	
			$PHP_OUTPUT.=('<td>');
			if ($curr_player->getAllianceID() > 0)
			{
				$container = array();
				$container['url']			= 'skeleton.php';
				$container['body']			= 'alliance_roster.php';
				$container['alliance_id']	= $curr_player->getAllianceID();
				$PHP_OUTPUT.=create_link($container, $curr_player->getAllianceName());
			}
			else
				$PHP_OUTPUT.=('(none)');
			$PHP_OUTPUT.=('</td>');
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = 'council_send_message.php';
			$container['race_id'] = $curr_player->getRaceID();
			$container['race_name'] = $curr_player->getRaceName();
			$PHP_OUTPUT.=('<td align="center" valign="middle">');
			$PHP_OUTPUT.=create_link($container, $player->getColouredRaceName($curr_player->getRaceID()));
			$PHP_OUTPUT.=('</td>');
			$PHP_OUTPUT.=('<td align="center" valign="middle">'.$curr_player->getExperience().'</td>');
			if ($curr_player->getLastCPLAction() > TIME - 600)
				$PHP_OUTPUT.=('<td width="10%" align="center" valign="middle" class="dgreen">YES</td>');
			else
				$PHP_OUTPUT.=('<td width="10%" align="center" valign="middle" class="red">NO</td>');
			if (in_array($player->getAccountID(), $HIDDEN_PLAYERS)) $PHP_OUTPUT.=('<td align="center" valign="middle">'.$curr_player->sector_id.'</td>');
			$PHP_OUTPUT.=('<td style="font-size:75%;" width="10%" class="center">');
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'message_send.php';
			$container['receiver']	= $curr_player->getAccountID();
			$PHP_OUTPUT.=create_link($container, '<span class="yellow">Send Message</span>');
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = 'bounty_view.php';
			$container['id'] = $curr_player->getAccountID();
			$PHP_OUTPUT.=create_link($container, '<br /><span class="yellow">View Bounty</span><br />');
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = 'hall_of_fame_player_detail.php';
			$container['account_id'] = $curr_player->getAccountID();
			$container['game_id'] = $curr_player->getGameID();
			$container['sending_page'] = 'search';
			$PHP_OUTPUT.=create_link($container, '<span class="yellow">View Stats</span><br />');
			if (in_array($player->getAccountID(), $HIDDEN_PLAYERS))
			{
				$container=array();
				$container['url'] = 'sector_jump_processing.php';
				$container['to'] = $curr_player->getSectorID();
				$PHP_OUTPUT.=create_link($container, '<span class="yellow">Jump to Sector</span>');
			}
			$PHP_OUTPUT.=('</td></tr>');
		}
		$PHP_OUTPUT.=('</table>');
		$count++;
	}
}
if ($count == 0)
	$PHP_OUTPUT.=('No Trader found!');

?>