<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPage;
use Smr\Template;

class VoteCreate extends AccountPage {

	public function __construct(
		private readonly ?string $previewVote = null,
		private readonly ?int $days = null,
		private readonly ?string $previewOption = null,
		private readonly ?int $voteID = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Create Vote';

		$voting = [];
		$currentOptions = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM voting WHERE end > :now ORDER BY vote_id DESC', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$voteID = $dbRecord->getInt('vote_id');
			$voting[$voteID]['ID'] = $voteID;
			$voting[$voteID]['Question'] = $dbRecord->getString('question');
		}
		$voteID = $this->voteID ?? array_key_first($voting);
		if ($voteID !== null) {
			$dbResult = $db->select(
				table: 'voting_options',
				criteria: ['vote_id' => $voteID],
				returnColumns: ['text'],
				orderBy: ['option_id'],
			);
			foreach ($dbResult->records() as $dbRecord) {
				$currentOptions[] = $dbRecord->getString('text');
			}
		}

		$template->pageRenderer = fn() => VoteCreateRenderer::render(
			VoteFormPage: new VoteCreateProcessor(),
			SelectVoteHREF: new VoteCreateSelectProcessor()->href(),
			CurrentVotes: $voting,
			CurrentOptions: $currentOptions,
			PreviewVote: $this->previewVote,
			Days: $this->days,
			PreviewOption: $this->previewOption,
			VoteID: $voteID,
		);
	}

}
