<?php declare(strict_types=1);
if (!isset($var['GameID'])) {
	SmrSession::updateVar('GameID', $player->getGameID());
}
$gameID = $var['GameID'];

$basicContainer = array('GameID'=>$gameID);

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
//			WHERE game_id = ' . $db->escapeNumber($gameID) . '
//		)
//		OR
//		alliance_id IN
//		(
//			SELECT DISTINCT dead_alliance
//			FROM news
//			WHERE game_id = ' . $db->escapeNumber($gameID) . '
//		)
//	)');
$db->query('SELECT alliance_id, alliance_name
			FROM alliance
			WHERE game_id = ' . $db->escapeNumber($gameID));

$newsAlliances = array();
$newsAlliances[0] = array('ID' => 0, 'Name' => 'None');
while ($db->nextRecord()) {
	$newsAlliances[$db->getInt('alliance_id')] = array('ID' => $db->getInt('alliance_id'), 'Name' => $db->getField('alliance_name'));
}
$template->assign('NewsAlliances', $newsAlliances);

$template->assign('AdvancedNewsFormHref', SmrSession::getNewHREF(create_container('skeleton.php', 'news_read_advanced.php', $basicContainer)));


if (isset($_REQUEST['submit'])) {
	$submit_value = $_REQUEST['submit'];
} elseif (isset($var['submit'])) {
	$submit_value = $var['submit'];
} else {
	$submit_value = 'Default';
}

if ($submit_value == 'Search For Player') {
	if (isset($_REQUEST['playerName'])) {
		$p_name = $_REQUEST['playerName'];
	} else {
		$p_name = $var['playerName'];
	}
	$template->assign('ResultsFor', $p_name);
	$db->query('SELECT * FROM player WHERE player_name LIKE ' . $db->escapeString('%' . $p_name . '%') . ' AND game_id = ' . $db->escapeNumber($gameID) . ' LIMIT 3');
	$IDs = array(0);
	while ($db->nextRecord()) {
		$IDs[] = $db->getInt('account_id');
	}
	$db->query('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND (killer_id IN (' . $db->escapeArray($IDs) . ') OR dead_id IN (' . $db->escapeArray($IDs) . ')) ORDER BY news_id DESC');
} elseif ($submit_value == 'Search For Alliance') {
	if (isset($_REQUEST['allianceID'])) {
		SmrSession::updateVar('AllianceID', $_REQUEST['allianceID']);
	}
	if (!isset($var['AllianceID'])) {
		create_error('No alliance was specified!');
	}
	$allianceID = $var['AllianceID'];
	$template->assign('ResultsFor', $newsAlliances[$allianceID]['Name']);
	$db->query('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND ((killer_alliance = ' . $db->escapeNumber($allianceID) . ' AND killer_id != ' . $db->escapeNumber(ACCOUNT_ID_PORT) . ') OR (dead_alliance = ' . $db->escapeNumber($allianceID) . ' AND dead_id != ' . $db->escapeNumber(ACCOUNT_ID_PORT) . ')) ORDER BY news_id DESC');
} elseif ($submit_value == 'Search For Players') {
	$template->assign('ResultsFor', $_REQUEST['player1'] . ' vs. ' . $_REQUEST['player2']);
	$db->query('SELECT * FROM player WHERE player_name LIKE ' . $db->escapeString('%' . $_REQUEST['player1'] . '%') . ' AND game_id = ' . $db->escapeNumber($gameID) . ' LIMIT 3');
	$IDs = array(0);
	while ($db->nextRecord()) {
		$IDs[] = $db->getInt('account_id');
	}
	$db->query('SELECT * FROM player WHERE player_name LIKE ' . $db->escapeString('%' . $_REQUEST['player2'] . '%') . ' AND game_id = ' . $db->escapeNumber($gameID) . ' LIMIT 3');
	$IDs2 = array(0);
	while ($db->nextRecord()) {
		$IDs2[] = $db->getInt('account_id');
	}
	$db->query('SELECT * FROM news
				WHERE game_id = ' . $db->escapeNumber($gameID) . '
					AND (
						(killer_id IN (' . $db->escapeArray($IDs) . ') AND dead_id IN (' . $db->escapeArray($IDs2) . '))
						OR
						(killer_id IN (' . $db->escapeArray($IDs2) . ') AND dead_id IN (' . $db->escapeArray($IDs) . '))
					) ORDER BY news_id DESC');
} elseif ($submit_value == 'Search For Alliances') {
	$template->assign('ResultsFor', $newsAlliances[$_REQUEST['alliance1']]['Name'] . ' vs. ' . $newsAlliances[$_REQUEST['alliance2']]['Name']);
	$db->query('SELECT * FROM news
				WHERE game_id = ' . $db->escapeNumber($gameID) . '
					AND (
						(killer_alliance = ' . $db->escapeNumber($_REQUEST['alliance1']) . ' AND dead_alliance = ' . $db->escapeNumber($_REQUEST['alliance2']) . ')
						OR
						(killer_alliance = ' . $db->escapeNumber($_REQUEST['alliance2']) . ' AND dead_alliance = ' . $db->escapeNumber($_REQUEST['alliance1']) . ')
					) ORDER BY news_id DESC');
} else {
	$db->query('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY news_id DESC LIMIT 50');
}

require_once(get_file_loc('news.functions.inc'));
$template->assign('NewsItems', getNewsItems($db));

$template->assign('PageTopic', 'Advanced News');
Menu::news($template);
