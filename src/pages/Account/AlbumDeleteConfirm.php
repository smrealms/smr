<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Template;

class AlbumDeleteConfirm extends AccountPage {

	public string $file = 'album_delete_confirmation.php';

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Delete Album Entry - Confirmation';
		$template->assign('CancelHref', (new AlbumEdit())->href());
		$template->assign('ConfirmHref', (new AlbumDeleteProcessor())->href());
	}

}
