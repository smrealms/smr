<?php declare(strict_types=1);

$action = Smr\Request::get('action');
if ($action == 'Preview Vote') {
	$container = Page::create('skeleton.php', 'admin/vote_create.php');
	$container['PreviewVote'] = Smr\Request::get('question');
	$container['Days'] = Smr\Request::getInt('days');
	$container->go();
}
if ($action == 'Preview Option') {
	$container = Page::create('skeleton.php', 'admin/vote_create.php');
	$container['PreviewOption'] = Smr\Request::get('option');
	$container['VoteID'] = Smr\Request::getInt('vote');
	$container->go();
}

$db = Smr\Database::getInstance();
if ($action == 'Create Vote') {
	$question = Smr\Request::get('question');
	$end = Smr\Epoch::time() + 86400 * Smr\Request::getInt('days');
	$db->insert('voting', [
		'question' => $db->escapeString($question),
		'end' => $db->escapeNumber($end),
	]);
} elseif ($action == 'Add Option') {
	$option = Smr\Request::get('option');
	$voteID = Smr\Request::getInt('vote');
	$db->insert('voting_options', [
		'vote_id' => $db->escapeNumber($voteID),
		'text' => $db->escapeString($option),
	]);
}
Page::create('skeleton.php', 'admin/vote_create.php')->go();
