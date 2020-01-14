<?php declare(strict_types=1);

$action = Request::get('action');
if ($action == 'Preview Vote') {
	$container = create_container('skeleton.php', 'vote_create.php');
	$container['PreviewVote'] = Request::get('question');
	$container['Days'] = Request::getInt('days');
	forward($container);
}
if ($action == 'Preview Option') {
	$container = create_container('skeleton.php', 'vote_create.php');
	$container['PreviewOption'] = Request::get('option');
	$container['VoteID'] = Request::getInt('vote');
	forward($container);
}

if ($action == 'Create Vote') {
	$question = trim(Request::get('question'));
	$end = TIME + 86400 * Request::getInt('days');
	$db->query('INSERT INTO voting (question, end) VALUES(' . $db->escapeString($question) . ',' . $db->escapeNumber($end) . ')');
} elseif ($action == 'Add Option') {
	$option = trim(Request::get('option'));
	$voteID = Request::getInt('vote');
	$db->query('INSERT INTO voting_options (vote_id, text) VALUES(' . $db->escapeNumber($voteID) . ',' . $db->escapeString($option) . ')');
}
forward(create_container('skeleton.php', 'vote_create.php'));
