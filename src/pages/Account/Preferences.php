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
	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Preferences';

		$session = Session::getInstance();
		if ($session->hasGame()) {
			$playerPreferences = [
				'Form' => new PlayerPreferencesProcessor(),
				'ChatSharingHREF' => (new ChatSharing())->href(),
				'Player' => $session->getPlayer(),
			];
		} else {
			$playerPreferences = null;
		}

		$template->pageRenderer = fn() => PreferencesRenderer::render(
			template: $template,
			PlayerPreferences: $playerPreferences,
			AccountPreferencesForm: new PreferencesProcessor(),
			TransferConfirmFormHREF: (new PreferencesTransferConfirm())->href(),
			ThisAccount: $account,
		);
	}

}
