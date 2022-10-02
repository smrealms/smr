<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Create Vote');

$template->assign('VoteFormHREF', Page::create('admin/vote_create_processing.php')->href());

$voting = [];
$db = Database::getInstance();
$dbResult = $db->read('SELECT * FROM voting WHERE end > ' . $db->escapeNumber(Epoch::time()));
foreach ($dbResult->records() as $dbRecord) {
	$voteID = $dbRecord->getInt('vote_id');
	$voting[$voteID]['ID'] = $voteID;
	$voting[$voteID]['Question'] = $dbRecord->getString('question');
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
