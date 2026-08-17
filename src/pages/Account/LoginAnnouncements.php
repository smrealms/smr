<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class LoginAnnouncements extends AccountPage {

	public function __construct(
		private readonly bool $viewAll = false,
	) {}

	public function build(Account $account, Template $template): void {
		$db = Database::getInstance();

		$template->pageTopic = 'Announcements';

		if (!$this->viewAll) {
			$dbResult = $db->read('SELECT time, msg
						FROM announcement
						WHERE time > :last_login
						ORDER BY time DESC', [
				'last_login' => $db->escapeNumber($account->getLastLogin()),
			]);
			$container = new LoginCheckChangelogProcessor();
		} else {
			$dbResult = $db->select('announcement', [], ['time', 'msg'], orderBy: ['time'], order: ['DESC']);
			$container = new GamePlay();
		}

		$announcements = [];
		foreach ($dbResult->records() as $dbRecord) {
			$announcements[] = [
				'Time' => $dbRecord->getInt('time'),
				'Msg' => htmlentities($dbRecord->getString('msg')),
			];
		}

		$template->pageRenderer = fn() => LoginAnnouncementsRenderer::render(
			Announcements: $announcements,
			ContinueHREF: $container->href(),
			ThisAccount: $account,
		);
	}

}
