<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Template;

class AnnouncementCreate extends AccountPage {

	public function __construct(
		private readonly ?string $preview = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Create Announcement';
		$template->pageRenderer = fn() => AnnouncementCreateRenderer::render(
			AnnouncementCreateForm: new AnnouncementCreateProcessor(),
			Preview: $this->preview === null ? null : htmlentities($this->preview),
		);
	}

}
