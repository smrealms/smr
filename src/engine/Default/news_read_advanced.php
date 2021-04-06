<?php declare(strict_types=1);

$session = Smr\Session::getInstance();

if (!isset($var['GameID'])) {
	$session->updateVar('GameID', $player->getGameID());
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
	$newsAlliances[$db->getInt('alliance_id')] = array('ID' => $db->getInt('alliance_id'), 'Name' => htmlentities($db->getField('alliance_name')));
}
$template->assign('NewsAlliances', $newsAlliances);

$template->assign('AdvancedNewsFormHref', Page::create('skeleton.php', 'news_read_advanced.php', $basicContainer)->href());

// No submit value when first navigating to the page
$submit_value = $session->getRequestVar('submit', '');

if ($submit_value == 'Search For Player') {
	$p_name = $session->getRequestVar('playerName');
	$template->assign('ResultsFor', $p_name);
	$db->query('SELECT * FROM player WHERE player_name LIKE ' . $db->escapeString('%' . $p_name . '%') . ' AND game_id = ' . $db->escapeNumber($gameID));
	$IDs = array(0);
	while ($db->nextRecord()) {
		$IDs[] = $db->getInt('account_id');
	}
	$db->query('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND (killer_id IN (' . $db->escapeArray($IDs) . ') OR dead_id IN (' . $db->escapeArray($IDs) . ')) ORDER BY news_id DESC');
} elseif ($submit_value == 'Search For Alliance') {
	$allianceID = $session->getRequestVarInt('allianceID');
	$template->assign('ResultsFor', $newsAlliances[$allianceID]['Name']);
	$db->query('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND ((killer_alliance = ' . $db->escapeNumber($allianceID) . ' AND killer_id != ' . $db->escapeNumber(ACCOUNT_ID_PORT) . ') OR (dead_alliance = ' . $db->escapeNumber($allianceID) . ' AND dead_id != ' . $db->escapeNumber(ACCOUNT_ID_PORT) . ')) ORDER BY news_id DESC');
} elseif ($submit_value == 'Search For Players') {
	$player1 = $session->getRequestVar('player1');
	$player2 = $session->getRequestVar('player2');
	$template->assign('ResultsFor', $player1 . ' vs. ' . $player2);
	$db->query('SELECT * FROM player WHERE player_name LIKE ' . $db->escapeString('%' . $player1 . '%') . ' AND game_id = ' . $db->escapeNumber($gameID));
	$IDs = array(0);
	while ($db->nextRecord()) {
		$IDs[] = $db->getInt('account_id');
	}
	$db->query('SELECT * FROM player WHERE player_name LIKE ' . $db->escapeString('%' . $player2 . '%') . ' AND game_id = ' . $db->escapeNumber($gameID));
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
	$allianceID1 = $session->getRequestVar('alliance1');
	$allianceID2 = $session->getRequestVar('alliance2');
	$template->assign('ResultsFor', $newsAlliances[$allianceID1]['Name'] . ' vs. ' . $newsAlliances[$allianceID2]['Name']);
	$db->query('SELECT * FROM news
				WHERE game_id = ' . $db->escapeNumber($gameID) . '
					AND (
						(killer_alliance = ' . $db->escapeNumber($allianceID1) . ' AND dead_alliance = ' . $db->escapeNumber($allianceID2) . ')
						OR
						(killer_alliance = ' . $db->escapeNumber($allianceID2) . ' AND dead_alliance = ' . $db->escapeNumber($allianceID1) . ')
					) ORDER BY news_id DESC');
} else {
	$db->query('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY news_id DESC LIMIT 50');
}

require_once(get_file_loc('news.inc.php'));
$template->assign('NewsItems', getNewsItems($db));

$template->assign('PageTopic', 'Advanced News');
Menu::news($template);
