<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class LogConsoleDetailProcessor extends AccountPageProcessor {

	/**
	 * @param array<int> $accountIDs
	 */
	public function __construct(
		private readonly array $accountIDs
	) {}

	public function build(SmrAccount $account): never {
		$logTypeIDs = Request::getIntArray('log_type_ids');
		(new LogConsoleDetail($this->accountIDs, $logTypeIDs))->go();
	}

}
