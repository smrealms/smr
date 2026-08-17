<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Changelog;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class ChangelogView extends AccountPage {

	use ReusableTrait;
	public function __construct(
		private readonly ?int $lastLogin = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Change Log';

		if ($this->lastLogin !== null) {
			$continueHREF = new LoginProcessor()->href();
		} else {
			$continueHREF = null;
		}

		$versions = Changelog::getDisplayVersions(
			since: $this->lastLogin ?? 0,
			dateFormat: $account->getDateTimeFormat(),
		);

		$template->pageRenderer = fn() => ChangelogViewRenderer::render(
			ContinueHREF: $continueHREF,
			Versions: $versions,
		);
	}

}
