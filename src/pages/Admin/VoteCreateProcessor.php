<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;
use Smr\Request;

$action = Request::get('action');
if ($action == 'Preview Vote') {
	$container = Page::create('admin/vote_create.php');
	$container['PreviewVote'] = Request::get('question');
	$container['Days'] = Request::getInt('days');
	$container->go();
}
if ($action == 'Preview Option') {
	$container = Page::create('admin/vote_create.php');
	$container['PreviewOption'] = Request::get('option');
	$container['VoteID'] = Request::getInt('vote');
	$container->go();
}

$db = Database::getInstance();
if ($action == 'Create Vote') {
	$question = Request::get('question');
	$end = Epoch::time() + 86400 * Request::getInt('days');
	$db->insert('voting', [
		'question' => $db->escapeString($question),
		'end' => $db->escapeNumber($end),
	]);
} elseif ($action == 'Add Option') {
	$option = Request::get('option');
	$voteID = Request::getInt('vote');
	$db->insert('voting_options', [
		'vote_id' => $db->escapeNumber($voteID),
		'text' => $db->escapeString($option),
	]);
}
Page::create('admin/vote_create.php')->go();
