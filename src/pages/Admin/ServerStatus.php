<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class ServerStatus extends AccountPage {

	public function build(Account $account, Template $template): void {
		$db = Database::getInstance();
		$dbResult = $db->select('game_disable');
		$serverIsOpen = !$dbResult->hasRecord();
		if ($serverIsOpen) {
			$template->pageTopic = 'Close Server';
		} else {
			$template->pageTopic = 'Open Server';
		}
		$template->pageRenderer = fn() => ServerStatusRenderer::render(
			ProcessingPage: new ServerStatusProcessor(),
			ServerIsOpen: $serverIsOpen,
		);
	}

}
