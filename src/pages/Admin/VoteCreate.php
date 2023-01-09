<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPage;
use Smr\Template;

class VoteCreate extends AccountPage {

	public string $file = 'admin/vote_create.php';

	public function __construct(
		private readonly ?string $previewVote = null,
		private readonly ?int $days = null,
		private readonly ?string $previewOption = null,
		private readonly ?int $voteID = null
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Create Vote');

		$template->assign('VoteFormHREF', (new VoteCreateProcessor())->href());

		$voting = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM voting WHERE end > ' . $db->escapeNumber(Epoch::time()));
		foreach ($dbResult->records() as $dbRecord) {
			$voteID = $dbRecord->getInt('vote_id');
			$voting[$voteID]['ID'] = $voteID;
			$voting[$voteID]['Question'] = $dbRecord->getString('question');
		}
		$template->assign('CurrentVotes', $voting);
		$template->assign('PreviewVote', $this->previewVote);
		$template->assign('Days', $this->days);
		$template->assign('PreviewOption', $this->previewOption);
		$template->assign('VoteID', $this->voteID);
	}

}
