<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Template;

class AnnouncementCreate extends AccountPage {

	public string $file = 'admin/announcement_create.php';

	public function __construct(
		private readonly ?string $preview = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Create Announcement';
		$template->assign('AnnouncementCreateForm', new AnnouncementCreateProcessor());
		if ($this->preview !== null) {
			$template->assign('Preview', htmlentities($this->preview));
		}
	}

}
