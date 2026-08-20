<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class VoteCreateProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionCreateVote;
	public readonly Submit $actionPreviewVote;
	public readonly Submit $actionAddOption;
	public readonly Submit $actionPreviewOption;

	public function __construct() {
		$this->actionCreateVote = new Submit(self::ACTION, 'Create Vote');
		$this->actionPreviewVote = new Submit(self::ACTION, 'Preview Vote');
		$this->actionAddOption = new Submit(self::ACTION, 'Add Option');
		$this->actionPreviewOption = new Submit(self::ACTION, 'Preview Option');
	}

	public function build(Account $account): never {
		$action = Request::get(self::ACTION);
		if ($action === $this->actionPreviewVote->value) {
			$container = new VoteCreate(
				previewVote: Request::get('question'),
				days: Request::getInt('days'),
			);
			$container->go();
		}
		if ($action === $this->actionPreviewOption->value) {
			$container = new VoteCreate(
				previewOption: Request::get('option'),
				voteID: Request::getInt('vote'),
			);
			$container->go();
		}

		$db = Database::getInstance();
		if ($action === $this->actionCreateVote->value) {
			$question = Request::get('question');
			$end = Epoch::time() + 86400 * Request::getInt('days');
			$db->insert('voting', [
				'question' => $question,
				'end' => $end,
			]);
		} elseif ($action === $this->actionAddOption->value) {
			$option = Request::get('option');
			$voteID = Request::getInt('vote');
			$db->insert('voting_options', [
				'vote_id' => $voteID,
				'text' => $option,
			]);
		}
		new VoteCreate()->go();
	}

}
