<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class ChangelogView extends AccountPage {

	use ReusableTrait;

	public string $file = 'changelog_view.php';

	public function __construct(
		private readonly ?int $lastLogin = null
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Change Log');

		if ($this->lastLogin !== null) {
			$container = new LoginProcessor();
			$template->assign('ContinueHREF', $container->href());
		}

		$db = Database::getInstance();

		$dbResult = $db->read('SELECT *
					FROM version
					WHERE went_live > :last_login
					ORDER BY version_id DESC', [
			'last_login' => $db->escapeNumber($this->lastLogin ?? 0),
		]);

		$versions = [];
		foreach ($dbResult->records() as $dbRecord) {
			$version_id = $dbRecord->getInt('version_id');
			$version = $dbRecord->getInt('major_version') . '.' . $dbRecord->getInt('minor_version') . '.' . $dbRecord->getInt('patch_level');
			$went_live = $dbRecord->getInt('went_live');

			// get human readable format for date
			if ($went_live > 0) {
				$went_live = date($account->getDateTimeFormat(), $went_live);
			} else {
				$went_live = 'never';
			}

			$dbResult2 = $db->read('SELECT *
						FROM changelog
						WHERE version_id = :version_id
						ORDER BY changelog_id', [
				'version_id' => $db->escapeNumber($version_id),
			]);
			$changes = [];
			foreach ($dbResult2->records() as $dbRecord2) {
				$changes[] = [
					'title' => $dbRecord2->getString('change_title'),
					'message' => $dbRecord2->getString('change_message'),
				];
			}

			$versions[] = [
				'version' => $version,
				'went_live' => $went_live,
				'changes' => $changes,
			];
		}
		$template->assign('Versions', $versions);
	}

}
