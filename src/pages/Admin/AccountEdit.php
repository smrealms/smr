<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class AccountEdit extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/account_edit.php';

	public function __construct(
		private readonly int $editAccountID
	) {}

	public function build(Account $account, Template $template): void {
		$db = Database::getInstance();

		$template->assign('PageTopic', 'Edit Account');

		$account_id = $this->editAccountID;
		$curr_account = Account::getAccount($account_id);

		$template->assign('EditingAccount', $curr_account);
		$template->assign('EditFormHREF', (new AccountEditProcessor($account_id))->href());
		$template->assign('ResetFormHREF', (new AccountEditSearch())->href());

		$editingPlayers = [];
		$dbResult = $db->read('SELECT * FROM player WHERE account_id = ' . $db->escapeNumber($curr_account->getAccountID()) . ' ORDER BY game_id ASC');
		foreach ($dbResult->records() as $dbRecord) {
			$editingPlayers[] = Player::getPlayer($curr_account->getAccountID(), $dbRecord->getInt('game_id'), false, $dbRecord);
		}
		$template->assign('EditingPlayers', $editingPlayers);

		$template->assign('Disabled', $curr_account->isDisabled());

		$banReasons = [];
		$dbResult = $db->read('SELECT * FROM closing_reason');
		foreach ($dbResult->records() as $dbRecord) {
			$reason = $dbRecord->getString('reason');
			if (strlen($reason) > 61) {
				$reason = substr($reason, 0, 61) . '...';
			}
			$banReasons[$dbRecord->getInt('reason_id')] = $reason;
		}
		$template->assign('BanReasons', $banReasons);

		$closingHistory = [];
		$dbResult = $db->read('SELECT * FROM account_has_closing_history WHERE account_id = ' . $db->escapeNumber($curr_account->getAccountID()) . ' ORDER BY time DESC');
		foreach ($dbResult->records() as $dbRecord) {
			// if an admin did it we get his/her name
			$admin_id = $dbRecord->getInt('admin_id');
			if ($admin_id > 0) {
				$admin = Account::getAccount($admin_id)->getLogin();
			} else {
				$admin = 'System';
			}
			$closingHistory[] = [
				'Time' => $dbRecord->getInt('time'),
				'Action' => $dbRecord->getString('action'),
				'AdminName' => $admin,
			];
		}
		$template->assign('ClosingHistory', $closingHistory);

		$dbResult = $db->read('SELECT * FROM account_exceptions WHERE account_id = ' . $curr_account->getAccountID());
		if ($dbResult->hasRecord()) {
			$template->assign('Exception', $dbResult->record()->getString('reason'));
		}

		$recentIPs = [];
		$dbResult = $db->read('SELECT ip, time, host FROM account_has_ip WHERE account_id = ' . $db->escapeNumber($curr_account->getAccountID()) . ' ORDER BY time DESC');
		foreach ($dbResult->records() as $dbRecord) {
			$recentIPs[] = [
				'IP' => $dbRecord->getString('ip'),
				'Time' => $dbRecord->getInt('time'),
				'Host' => $dbRecord->getString('host'),
			];
		}
		$template->assign('RecentIPs', $recentIPs);
	}

}
