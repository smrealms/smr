<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Changelog;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class ChangelogAdd extends AccountPage {

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

		$versions = Changelog::getDisplayVersions(
			since: -1, // include draft versions
			dateFormat: $account->getDateTimeFormat(),
		);

		$first_entry = true;

		foreach ($versions as $version_id => $version) {
			if ($version['went_live'] === null) {
				$container = new ChangelogSetLiveProcessor($version_id);
				$version['went_live'] = create_link($container, 'never');
			}

			if ($first_entry) {
				$first_entry = false;
				$container = new ChangelogAddProcessor($version_id);
				$template->assign('AddHREF', $container->href());

				if ($this->changeTitle !== '') {
					$version['changes'][] = [
						'title' => '<span class="red">PREVIEW: </span>' . htmlentities($this->changeTitle),
						'message' => bbifyMessage(htmlentities($this->changeMessage)),
					];
				}
				$template->assign('FirstVersion', $version);
				unset($versions[$version_id]);
			}
		}
		$template->assign('Versions', $versions);
	}

}
