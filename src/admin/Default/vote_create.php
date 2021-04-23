<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Create Vote');

$template->assign('VoteFormHREF', Page::create('vote_create_processing.php', '')->href());

$voting = array();
$db = Smr\Database::getInstance();
$db->query('SELECT * FROM voting WHERE end > ' . $db->escapeNumber(Smr\Epoch::time()));
while ($db->nextRecord()) {
	$voteID = $db->getInt('vote_id');
	$voting[$voteID]['ID'] = $voteID;
	$voting[$voteID]['Question'] = $db->getField('question');
}
$template->assign('CurrentVotes', $voting);
if (isset($var['PreviewVote'])) {
	$template->assign('PreviewVote', $var['PreviewVote']);
}
if (isset($var['Days'])) {
	$template->assign('Days', $var['Days']);
}
if (isset($var['PreviewOption'])) {
	$template->assign('PreviewOption', $var['PreviewOption']);
}
if (isset($var['VoteID'])) {
	$template->assign('VoteID', $var['VoteID']);
}
