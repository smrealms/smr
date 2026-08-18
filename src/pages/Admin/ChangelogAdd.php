<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Changelog;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class ChangelogAdd extends AccountPage {

	use ReusableTrait;
	public function __construct(
		private readonly string $changeTitle = '',
		private readonly string $changeMessage = '',
		private readonly string $affectedDb = '',
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Change Log';

		$versions = Changelog::getDisplayVersions(
			since: -1, // include draft versions
			dateFormat: $account->getDateTimeFormat(),
		);
		if (count($versions) === 0) {
			$template->pageRenderer = fn() => ChangelogAddRenderer::renderEmpty();
			return;
		}

		$first_entry = true;
		$addPage = null;
		$firstVersion = null;
		foreach ($versions as $version_id => $version) {
			if ($version['went_live'] === null) {
				$container = new ChangelogSetLiveProcessor($version_id);
				$version['went_live'] = create_link($container, 'never');
			}

			if ($first_entry) {
				$first_entry = false;
				$addPage = new ChangelogAddProcessor($version_id);

				if ($this->changeTitle !== '') {
					$version['changes'][] = [
						'title' => '<span class="red">PREVIEW: </span>' . htmlentities($this->changeTitle),
						'message' => bbify(htmlentities($this->changeMessage)),
					];
				}
				$firstVersion = $version;
				unset($versions[$version_id]);
			}
		}

		$template->pageRenderer = fn() => ChangelogAddRenderer::render(
			ChangeTitle: $this->changeTitle,
			ChangeMessage: $this->changeMessage,
			AffectedDb: $this->affectedDb,
			AddPage: $addPage,
			FirstVersion: $firstVersion,
			Versions: $versions,
		);
	}

}
