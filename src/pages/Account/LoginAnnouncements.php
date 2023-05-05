<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class LoginAnnouncements extends AccountPage {

	public string $file = 'announcements.php';

	public function __construct(
		private readonly bool $viewAll = false
	) {}

	public function build(Account $account, Template $template): void {
		$db = Database::getInstance();

		$template->assign('PageTopic', 'Announcements');

		if (!$this->viewAll) {
			$dbResult = $db->read('SELECT time, msg
						FROM announcement
						WHERE time > :last_login
						ORDER BY time DESC', [
				'last_login' => $db->escapeNumber($account->getLastLogin()),
			]);
			$container = new LoginCheckChangelogProcessor();
		} else {
			$dbResult = $db->read('SELECT time, msg
						FROM announcement
						ORDER BY time DESC');
			$container = new GamePlay();
		}

		$announcements = [];
		foreach ($dbResult->records() as $dbRecord) {
			$announcements[] = [
				'Time' => $dbRecord->getInt('time'),
				'Msg' => htmlentities($dbRecord->getString('msg')),
			];
		}
		$template->assign('Announcements', $announcements);

		$template->assign('ContinueHREF', $container->href());
	}

}
