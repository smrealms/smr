<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class AccountCloseProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		// Number of banned accounts
		$amount = 0;

		// Disabling from the "Computer Sharing" page
		if (Request::has('close')) {
			//never expire
			$expire_time = 0;
			foreach (Request::getArray('close') as $key => $value) {
				$val = 'Match list:' . $value;
				$bannedAccount = Account::getAccount($key);
				$bannedAccount->banAccount($expire_time, $account, BAN_REASON_MULTI, $val);
				$amount++;
			}
		}

		if (Request::has('first')) {
			$same_ip = Request::getIntArray('same_ip');
			$val = 'Match list:' . implode(',', $same_ip);
			foreach ($same_ip as $account_id) {
				//never expire
				$bannedAccount = Account::getAccount($account_id);
				$bannedAccount->banAccount(0, $account, BAN_REASON_MULTI, $val);
				$amount++;
			}
		}

		// Disabling from the "List all IPs for a specific account" page
		if (Request::has('second')) {
			//never expire
			$bannedAccount = Account::getAccount(Request::getInt('second'));
			$bannedAccount->banAccount(0, $account, BAN_REASON_MULTI, Request::get('reason'));
			$amount++;
		}

		// Disabling from the "List all IPs" page
		if (Request::has('disable_id')) {
			$reasons = Request::getArray('suspicion');
			$reasons2 = Request::getArray('suspicion2');
			foreach (Request::getIntArray('disable_id') as $id) {

				$reason = $reasons[$id];
				if (empty($reason)) {
					$reason = $reasons2[$id];
				}

				//never expire
				$bannedAccount = Account::getAccount($id);
				$bannedAccount->banAccount(0, $account, BAN_REASON_MULTI, $reason);
				$amount++;
			}
		}

		$msg = 'You have disabled ' . $amount . ' accounts.';
		if ($amount > 20) {
			$msg .= '  How do you sleep at night ;)';
		}
		$container = new AdminTools($msg);
		$container->go();
	}

}
