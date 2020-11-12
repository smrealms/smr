<?php declare(strict_types=1);
$template->assign('PageTopic', 'Voting');

$db->query('SELECT * FROM voting ORDER BY end DESC');
if ($db->getNumRows() > 0) {
	$db2 = MySqlDatabase::getInstance(true);
	$votedFor = array();
	$db2->query('SELECT * FROM voting_results WHERE account_id = ' . $db2->escapeNumber($account->getAccountID()));
	while ($db2->nextRecord()) {
		$votedFor[$db2->getInt('vote_id')] = $db2->getInt('option_id');
	}
	$voting = array();
	while ($db->nextRecord()) {
		$voteID = $db->getInt('vote_id');
		$voting[$voteID]['ID'] = $voteID;
		$container = create_container('vote.php', 'vote_processing.php');
		$container['vote_id'] = $voteID;
		$voting[$voteID]['HREF'] = SmrSession::getNewHREF($container);
		$voting[$voteID]['Question'] = $db->getField('question');
		if ($db->getInt('end') > TIME) {
			$voting[$voteID]['TimeRemaining'] = format_time($db->getInt('end') - TIME, true);
		} else {
			$voting[$voteID]['EndDate'] = date(DATE_DATE_SHORT, $db->getInt('end'));
		}
		$voting[$voteID]['Options'] = array();
		$db2->query('SELECT option_id,text,count(account_id) FROM voting_options LEFT OUTER JOIN voting_results USING(vote_id,option_id) WHERE vote_id = ' . $db2->escapeNumber($db->getInt('vote_id')) . ' GROUP BY option_id');
		while ($db2->nextRecord()) {
			$voting[$voteID]['Options'][$db2->getInt('option_id')]['ID'] = $db2->getInt('option_id');
			$voting[$voteID]['Options'][$db2->getInt('option_id')]['Text'] = $db2->getField('text');
			$voting[$voteID]['Options'][$db2->getInt('option_id')]['Chosen'] = isset($votedFor[$db->getInt('vote_id')]) && $votedFor[$voteID] == $db2->getInt('option_id');
			$voting[$voteID]['Options'][$db2->getInt('option_id')]['Votes'] = $db2->getInt('count(account_id)');
		}
	}
	$template->assign('Voting', $voting);
}
