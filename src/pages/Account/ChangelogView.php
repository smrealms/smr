<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Changelog;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class ChangelogView extends AccountPage {

	use ReusableTrait;

	public string $file = 'changelog_view.php';

	public function __construct(
		private readonly ?int $lastLogin = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Change Log');

		if ($this->lastLogin !== null) {
			$container = new LoginProcessor();
			$template->assign('ContinueHREF', $container->href());
		}

		$versions = Changelog::getDisplayVersions(
			since: $this->lastLogin ?? 0,
			dateFormat: $account->getDateTimeFormat(),
		);
		$template->assign('Versions', $versions);
	}

}
