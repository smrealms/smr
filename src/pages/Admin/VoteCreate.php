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
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM voting WHERE end > :now', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$voteID = $dbRecord->getInt('vote_id');
			$voting[$voteID]['ID'] = $voteID;
			$voting[$voteID]['Question'] = $dbRecord->getString('question');
		}

		$template->pageRenderer = fn() => VoteCreateRenderer::render(
			VoteFormPage: new VoteCreateProcessor(),
			CurrentVotes: $voting,
			PreviewVote: $this->previewVote,
			Days: $this->days,
			PreviewOption: $this->previewOption,
			VoteID: $this->voteID,
		);
	}

}
