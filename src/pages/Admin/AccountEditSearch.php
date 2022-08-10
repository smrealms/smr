<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;
use SmrAccount;

class AccountEditSearch extends AccountPage {

	public string $file = 'admin/account_edit_search.php';

	public function __construct(
		private readonly ?string $message = null,
		private readonly ?string $errorMessage = null
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Edit Account');

		$games = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_id, game_name FROM game WHERE enabled = \'TRUE\' ORDER BY game_id DESC');
		foreach ($dbResult->records() as $dbRecord) {
			$gameID = $dbRecord->getInt('game_id');
			$games[$gameID] = $dbRecord->getString('game_name') . ' (' . $gameID . ')';
		}
		$template->assign('Games', $games);
		$template->assign('SearchHREF', (new AccountEditSearchProcessor())->href());

		$template->assign('ErrorMessage', $this->errorMessage);
		$template->assign('Message', $this->message);
	}

}
