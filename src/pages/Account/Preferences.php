<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Player\ChatSharing;
use Smr\Pages\Player\PreferencesProcessor as PlayerPreferencesProcessor;
use Smr\Session;
use Smr\Template;

class Preferences extends AccountPage {

	use ReusableTrait;

	public string $file = 'preferences.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Preferences');

		$session = Session::getInstance();
		if ($session->hasGame()) {
			$template->assign('PlayerPreferencesForm', new PlayerPreferencesProcessor());
			$template->assign('ChatSharingHREF', (new ChatSharing())->href());
		}
		$template->assign('AccountPreferencesForm', new PreferencesProcessor());

		$template->assign('TransferConfirmFormHREF', (new PreferencesTransferConfirm())->href());
	}

}
