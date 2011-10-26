<?php
if(!isset($var['GameID'])) SmrSession::updateVar('GameID',$player->getGameID());
$gameID = $var['GameID'];

$basicContainer = array('GameID'=>$gameID);

$template->assign('PageTopic','Advanced News');
require_once(get_file_loc('menu.inc'));
create_news_menu($template);

//$db->query('
//SELECT alliance_id, alliance_name
//FROM alliance
//WHERE game_id = ' . $gameID . '
//	AND
//	(
//		alliance_id IN
//		(
//			SELECT DISTINCT killer_alliance
//			FROM news
//			WHERE game_id = ' . $gameID . '
//		)
//		OR
//		alliance_id IN
//		(
//			SELECT DISTINCT dead_alliance
//			FROM news
//			WHERE game_id = ' . $gameID . '
//		)
//	)');
$db->query('
SELECT alliance_id, alliance_name
FROM alliance
WHERE game_id = ' . $gameID);

$newsAlliances = array();
$newsAlliances[0] = array('ID' => 0, 'Name' => 'None');
while($db->nextRecord())
{
	$newsAlliances[$db->getField('alliance_id')] = array('ID' => $db->getField('alliance_id'), 'Name' => $db->getField('alliance_name'));
}
$template->assign('NewsAlliances',$newsAlliances);

$template->assign('AdvancedNewsFormHref',SmrSession::get_new_href(create_container('skeleton.php','news_read_advanced.php',$basicContainer)));


if (isset($_REQUEST['submit'])) $submit_value = $_REQUEST['submit'];
elseif (isset($var['submit'])) $submit_value = $var['submit'];
else $submit_value = 'Default';

if ($submit_value == 'Search For Player')
{
	if (isset($_REQUEST['playerName'])) $p_name = $_REQUEST['playerName'];
	else $p_name = $var['playerName'];
	$PHP_OUTPUT .= 'Returning Results for ' . $p_name . '.<br />';
	$db->query('SELECT * FROM player WHERE player_name LIKE ' . $db->escapeString('%'.$p_name.'%') . ' AND game_id = ' . $gameID.' LIMIT 3');
	$IDs = array(0);
	while($db->nextRecord())
	{
		$IDs[] = $db->getField('account_id');
	}
	$db->query('SELECT * FROM news WHERE game_id = ' . $gameID . ' AND (killer_id IN (' . $db->escapeArray($IDs) . ') OR dead_id IN (' . $db->escapeString($IDs) . ')) ORDER BY news_id DESC');
}
elseif ($submit_value == 'Search For Alliance')
{
	if (isset($_REQUEST['allianceID'])) SmrSession::updateVar('AllianceID',$_REQUEST['allianceID']);
	if(!isset($var['AllianceID'])) create_error('No alliance was specified!');
	$allianceID = $var['AllianceID'];
	$PHP_OUTPUT .= 'Returning Results for ' . $newsAlliances[$allianceID]['Name'] . '.<br />';
	$db->query('SELECT * FROM news WHERE game_id = ' . $gameID . ' AND ((killer_alliance = ' . $db->escapeNumber($allianceID) . ' AND killer_id != '.ACCOUNT_ID_PORT.') OR (dead_alliance = ' . $db->escapeNumber($allianceID) . ' AND dead_id != '.ACCOUNT_ID_PORT.')) ORDER BY news_id DESC');
}
elseif ($submit_value == 'Search For Players')
{
	$PHP_OUTPUT .= 'Returning Results for ' . $_REQUEST['player1'] . ' vs. ' . $_REQUEST['player2'] . '<br />';
	$db->query('SELECT * FROM player WHERE player_name LIKE ' . $db->escapeString('%'.$_REQUEST['player1'].'%') . ' AND game_id = ' . $gameID.' LIMIT 3');
	$IDs = array(0);
	while($db->nextRecord())
	{
		$IDs[] = $db->getField('account_id');
	}
	$db->query('SELECT * FROM player WHERE player_name LIKE ' . $db->escapeString('%'.$_REQUEST['player2'].'%') . ' AND game_id = ' . $gameID.' LIMIT 3');
	$IDs2 = array(0);
	while($db->nextRecord())
	{
		$IDs2[] = $db->getField('account_id');
	}
	$db->query('SELECT * FROM news WHERE game_id = ' . $gameID .
					' AND (
							(killer_id IN (' . $db->escapeArray($IDs) . ') AND dead_id IN (' . $db->escapeArray($IDs2) . '))
							OR
							(killer_id IN (' . $db->escapeArray($IDs2) . ') AND dead_id IN (' . $db->escapeArray($IDs) . '))
						) ORDER BY news_id DESC');
}
elseif ($submit_value == 'Search For Alliances')
{
	$PHP_OUTPUT .= 'Returning Results for ' . $newsAlliances[$_REQUEST['alliance1']]['Name'] . ' vs. ' . $newsAlliances[$_REQUEST['alliance2']]['Name'] . '<br />';
	$db->query('SELECT * FROM news WHERE game_id = ' . $gameID .
					' AND (
							(killer_alliance IN (' . $db->escapeNumber($_REQUEST['alliance1']) . ') AND dead_alliance IN (' . $db->escapeNumber($_REQUEST['alliance2']) . '))
							OR
							(killer_alliance IN (' . $db->escapeNumber($_REQUEST['alliance2']) . ') AND dead_alliance IN (' . $db->escapeNumber($_REQUEST['alliance1']) . '))
						) ORDER BY news_id DESC');
}
else
{
	$db->query('SELECT * FROM news WHERE game_id = ' . $gameID . ' ORDER BY news_id DESC LIMIT 50');
}

if ($db->getNumRows())
{
	$NewsItems = array();
	while ($db->nextRecord())
	{
		$NewsItems[] = array('Time' => $db->getField('time'), 'Message' => bbifyMessage($db->getField('news_message')));
	}
	$template->assign('NewsItems',$NewsItems);
}
?>