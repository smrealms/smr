<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
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
			$template->assign('PlayerPreferencesFormHREF', (new PlayerPreferencesProcessor())->href());
			$template->assign('ChatSharingHREF', (new ChatSharing())->href());
		}
		$template->assign('AccountPreferencesFormHREF', (new PreferencesProcessor())->href());

		$template->assign('PreferencesConfirmFormHREF', (new PreferencesTransferConfirm())->href());

		$transferAccounts = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT account_id,hof_name FROM account WHERE validated = :validated ORDER BY hof_name', [
			'validated' => $db->escapeBoolean(true),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$transferAccounts[$dbRecord->getInt('account_id')] = htmlspecialchars($dbRecord->getString('hof_name'));
		}
		$template->assign('TransferAccounts', $transferAccounts);
	}

}
