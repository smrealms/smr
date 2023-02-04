<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class VoteCreateProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$action = Request::get('action');
		if ($action == 'Preview Vote') {
			$container = new VoteCreate(
				previewVote: Request::get('question'),
				days: Request::getInt('days'),
			);
			$container->go();
		}
		if ($action == 'Preview Option') {
			$container = new VoteCreate(
				previewOption: Request::get('option'),
				voteID: Request::getInt('vote'),
			);
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
		(new VoteCreate())->go();
	}

}
