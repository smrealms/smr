<?

$template->assign('PageTopic','Create Vote');

$template->assign('VoteFormHREF',SmrSession::get_new_href(create_container('vote_create_processing.php', '')));

$db->query('SELECT * FROM voting WHERE end > ' . TIME);
while ($db->nextRecord())
{
	$voteID = $db->getField('vote_id');
	$voting[$voteID]['ID'] = $voteID;
	$voting[$voteID]['Question'] = $db->getField('question');
}
$template->assign('CurrentVotes',$voting);
?>