<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Template;

class AlbumDeleteConfirm extends AccountPage {

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Delete Album Entry - Confirmation';
		$template->pageRenderer = fn() => AlbumDeleteConfirmRenderer::render(
			CancelHref: new AlbumEdit()->href(),
			ConfirmHref: new AlbumDeleteProcessor()->href(),
		);
	}

}
