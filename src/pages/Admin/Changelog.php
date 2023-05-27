<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class Changelog extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/changelog.php';

	public function __construct(
		private readonly string $changeTitle = '',
		private readonly string $changeMessage = '',
		private readonly string $affectedDb = '',
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Change Log');

		$template->assign('ChangeTitle', $this->changeTitle);
		$template->assign('ChangeMessage', $this->changeMessage);
		$template->assign('AffectedDb', $this->affectedDb);

		$first_entry = true;
		$link_set_live = true;

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM version ORDER BY version_id DESC');

		$versions = [];
		foreach ($dbResult->records() as $dbRecord) {
			$version_id = $dbRecord->getInt('version_id');
			$version = $dbRecord->getInt('major_version') . '.' . $dbRecord->getInt('minor_version') . '.' . $dbRecord->getInt('patch_level');
			$went_live = $dbRecord->getInt('went_live');
			if ($went_live > 0) {
				// from this point on we don't create links to set a version to live
				$link_set_live = false;

				// get human readable format for date
				$went_live = date($account->getDateTimeFormat(), $went_live);
			} else {
				if ($link_set_live) {
					$container = new ChangelogSetLiveProcessor($version_id);
					$went_live = create_link($container, 'never');
				} else {
					$went_live = 'never';
				}
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

			$version = [
				'version' => $version,
				'went_live' => $went_live,
				'changes' => $changes,
			];

			if ($first_entry) {
				$first_entry = false;
				$container = new ChangelogAddProcessor($version_id);
				$template->assign('AddHREF', $container->href());

				if ($this->changeTitle !== '') {
					$version['changes'][] = [
						'title' => '<span class="red">PREVIEW: </span>' . $this->changeTitle,
						'message' => $this->changeMessage,
					];
				}
				$template->assign('FirstVersion', $version);
			} else {
				$versions[] = $version;
			}
		}
		$template->assign('Versions', $versions);
	}

}
