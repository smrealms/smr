<?php
$template->assign('PageTopic','Voting');

$db->query('SELECT * FROM voting ORDER BY end DESC');
if($db->getNumRows()>0)
{
	$db2 = new SmrMySqlDatabase();
	$votedFor=array();
	$db2->query('SELECT * FROM voting_results WHERE account_id = ' . $account->getAccountID());
	while ($db2->nextRecord())
	{
		$votedFor[$db2->getField('vote_id')] = $db2->getField('option_id');
	}
	$voting = array();
	while ($db->nextRecord())
	{
		$voteID = $db->getField('vote_id');
		$voting[$voteID]['ID'] = $voteID;
		$container = array();
		$container['body'] = 'vote.php';
		$container['url'] = 'vote_processing.php';
		$container['vote_id'] = $voteID;
		$voting[$voteID]['HREF'] = SmrSession::get_new_href($container);
		$voting[$voteID]['Question'] = $db->getField('question');
		if($db->getField('end') > TIME)
			$voting[$voteID]['TimeRemaining'] = format_time($db->getField('end') - TIME, true);
		$voting[$voteID]['Options'] = array();
		$db2->query('SELECT option_id,text,count(account_id) FROM voting_options LEFT OUTER JOIN voting_results USING(vote_id,option_id) WHERE vote_id = ' . $db->getField('vote_id').' GROUP BY option_id');
		while ($db2->nextRecord())
		{
			$voting[$voteID]['Options'][$db2->getField('option_id')]['ID'] = $db2->getField('option_id');
			$voting[$voteID]['Options'][$db2->getField('option_id')]['Text'] = $db2->getField('text');
			$voting[$voteID]['Options'][$db2->getField('option_id')]['Chosen'] = isset($votedFor[$db->getField('vote_id')]) && $votedFor[$voteID] == $db2->getField('option_id');
			$voting[$voteID]['Options'][$db2->getField('option_id')]['Votes'] = $db2->getField('count(account_id)');
		}
	}
	$template->assign('Voting',$voting);
}

?>