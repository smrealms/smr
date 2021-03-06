<?php declare(strict_types=1);

$action = Request::get('action');
if ($action == 'Preview Vote') {
	$container = Page::create('skeleton.php', 'vote_create.php');
	$container['PreviewVote'] = Request::get('question');
	$container['Days'] = Request::getInt('days');
	$container->go();
}
if ($action == 'Preview Option') {
	$container = Page::create('skeleton.php', 'vote_create.php');
	$container['PreviewOption'] = Request::get('option');
	$container['VoteID'] = Request::getInt('vote');
	$container->go();
}

$db = Smr\Database::getInstance();
if ($action == 'Create Vote') {
	$question = trim(Request::get('question'));
	$end = Smr\Epoch::time() + 86400 * Request::getInt('days');
	$db->write('INSERT INTO voting (question, end) VALUES(' . $db->escapeString($question) . ',' . $db->escapeNumber($end) . ')');
} elseif ($action == 'Add Option') {
	$option = trim(Request::get('option'));
	$voteID = Request::getInt('vote');
	$db->write('INSERT INTO voting_options (vote_id, text) VALUES(' . $db->escapeNumber($voteID) . ',' . $db->escapeString($option) . ')');
}
Page::create('skeleton.php', 'vote_create.php')->go();
