<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class LogConsoleDetailProcessor extends AccountPageProcessor {

	/**
	 * @param array<int> $accountIDs
	 */
	public function __construct(
		private readonly array $accountIDs
	) {}

	public function build(Account $account): never {
		$logTypeIDs = Request::getIntArray('log_type_ids');
		(new LogConsoleDetail($this->accountIDs, $logTypeIDs))->go();
	}

}
