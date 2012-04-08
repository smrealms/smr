<?php

$template->assign('PageTopic','Create Vote');

$template->assign('VoteFormHREF',SmrSession::get_new_href(create_container('vote_create_processing.php', '')));

$voting = array();
$db->query('SELECT * FROM voting WHERE end > ' . $db->escapeNumber(TIME));
while ($db->nextRecord())
{
	$voteID = $db->getField('vote_id');
	$voting[$voteID]['ID'] = $voteID;
	$voting[$voteID]['Question'] = $db->getField('question');
}
$template->assign('CurrentVotes',$voting);
if(isset($var['PreviewVote']))
	$template->assign('PreviewVote', $var['PreviewVote']);
if(isset($var['Days']))
	$template->assign('Days', $var['Days']);
if(isset($var['PreviewOption']))
	$template->assign('PreviewOption', $var['PreviewOption']);
if(isset($var['VoteID']))
	$template->assign('VoteID', $var['VoteID']);
?>