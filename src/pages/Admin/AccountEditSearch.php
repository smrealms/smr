<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class AccountEditSearch extends AccountPage {

	public function __construct(
		private readonly ?string $message = null,
		private readonly ?string $errorMessage = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Edit Account';

		$games = [];
		$db = Database::getInstance();
		$dbResult = $db->select(
			'game',
			['enabled' => $db->escapeBoolean(true)],
			['game_id', 'game_name'],
			['game_id'],
			['DESC'],
		);
		foreach ($dbResult->records() as $dbRecord) {
			$gameID = $dbRecord->getInt('game_id');
			$games[$gameID] = $dbRecord->getString('game_name') . ' (' . $gameID . ')';
		}

		$template->pageRenderer = fn() => AccountEditSearchRenderer::render(
			Games: $games,
			SearchHREF: new AccountEditSearchProcessor()->href(),
			ErrorMessage: $this->errorMessage,
			Message: $this->message,
		);
	}

}
