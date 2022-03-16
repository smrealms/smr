<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$gameID = $var['GameID'] ?? $session->getPlayer()->getGameID();

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT alliance_id, alliance_name
			FROM alliance
			WHERE game_id = ' . $db->escapeNumber($gameID));

$newsAlliances = [];
$newsAlliances[0] = ['ID' => 0, 'Name' => 'None'];
foreach ($dbResult->records() as $dbRecord) {
	$newsAlliances[$dbRecord->getInt('alliance_id')] = ['ID' => $dbRecord->getInt('alliance_id'), 'Name' => htmlentities($dbRecord->getString('alliance_name'))];
}
$template->assign('NewsAlliances', $newsAlliances);

$template->assign('AdvancedNewsFormHref', Page::create('skeleton.php', 'news_read_advanced.php', ['GameID' => $gameID])->href());

// No submit value when first navigating to the page
$submit_value = $session->getRequestVar('submit', '');

if ($submit_value == 'Search For Player') {
	$p_name = $session->getRequestVar('playerName');
	$template->assign('ResultsFor', $p_name);
	$dbResult = $db->read('SELECT account_id FROM player WHERE player_name LIKE ' . $db->escapeString('%' . $p_name . '%') . ' AND game_id = ' . $db->escapeNumber($gameID));
	$IDs = [0];
	foreach ($dbResult->records() as $dbRecord) {
		$IDs[] = $dbRecord->getInt('account_id');
	}
	$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND (killer_id IN (' . $db->escapeArray($IDs) . ') OR dead_id IN (' . $db->escapeArray($IDs) . ')) ORDER BY news_id DESC');
} elseif ($submit_value == 'Search For Alliance') {
	$allianceID = $session->getRequestVarInt('allianceID');
	$template->assign('ResultsFor', $newsAlliances[$allianceID]['Name']);
	$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND ((killer_alliance = ' . $db->escapeNumber($allianceID) . ' AND killer_id != ' . $db->escapeNumber(ACCOUNT_ID_PORT) . ') OR (dead_alliance = ' . $db->escapeNumber($allianceID) . ' AND dead_id != ' . $db->escapeNumber(ACCOUNT_ID_PORT) . ')) ORDER BY news_id DESC');
} elseif ($submit_value == 'Search For Players') {
	$player1 = $session->getRequestVar('player1');
	$player2 = $session->getRequestVar('player2');
	$template->assign('ResultsFor', $player1 . ' vs. ' . $player2);
	$dbResult = $db->read('SELECT account_id FROM player WHERE (player_name LIKE ' . $db->escapeString('%' . $player1 . '%') . ' OR player_name LIKE ' . $db->escapeString('%' . $player2 . '%') . ') AND game_id = ' . $db->escapeNumber($gameID));
	$IDs = [0];
	foreach ($dbResult->records() as $dbRecord) {
		$IDs[] = $dbRecord->getInt('account_id');
	}
	$dbResult = $db->read('SELECT * FROM news
				WHERE game_id = ' . $db->escapeNumber($gameID) . '
					AND (
						killer_id IN (' . $db->escapeArray($IDs) . ') AND dead_id IN (' . $db->escapeArray($IDs) . ')
					) ORDER BY news_id DESC');
} elseif ($submit_value == 'Search For Alliances') {
	$allianceID1 = $session->getRequestVar('alliance1');
	$allianceID2 = $session->getRequestVar('alliance2');
	$template->assign('ResultsFor', $newsAlliances[$allianceID1]['Name'] . ' vs. ' . $newsAlliances[$allianceID2]['Name']);
	$dbResult = $db->read('SELECT * FROM news
				WHERE game_id = ' . $db->escapeNumber($gameID) . '
					AND (
						(killer_alliance = ' . $db->escapeNumber($allianceID1) . ' AND dead_alliance = ' . $db->escapeNumber($allianceID2) . ')
						OR
						(killer_alliance = ' . $db->escapeNumber($allianceID2) . ' AND dead_alliance = ' . $db->escapeNumber($allianceID1) . ')
					) ORDER BY news_id DESC');
} else {
	$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY news_id DESC LIMIT 50');
}

$template->assign('NewsItems', Smr\News::getNewsItems($dbResult));

$template->assign('PageTopic', 'Advanced News');
Menu::news($gameID);
