<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Page\AccountPage;
use Smr\Template;
use SmrAccount;

class AnnouncementCreate extends AccountPage {

	public string $file = 'admin/announcement_create.php';

	public function __construct(
		private readonly ?string $preview = null
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Create Announcement');
		$template->assign('AnnouncementCreateFormHref', (new AnnouncementCreateProcessor())->href());
		if ($this->preview !== null) {
			$template->assign('Preview', htmlentities($this->preview));
		}
	}

}
