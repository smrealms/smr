<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\Page;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AllianceBankExemptProcessor extends PlayerPageProcessor {

	/**
	 * @param list<int> $displayedTransactionIDs
	 */
	public function __construct(
		private readonly Page $forwardTo,
		private readonly array $displayedTransactionIDs = [],
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();

		// Unexempt all displayed transactions first, then exempt selected below.
		if (count($this->displayedTransactionIDs) > 0) {
			$db->write('UPDATE alliance_bank_transactions SET exempt = 0 WHERE
						transaction_id IN (:transaction_ids)', [
				'transaction_ids' => $db->escapeArray($this->displayedTransactionIDs),
			]);
		}

		if (Request::has('exempt')) {
			$trans_ids = array_keys(Request::getArray('exempt'));
			$db->write('UPDATE alliance_bank_transactions SET exempt = 1, request_exempt = 0 WHERE
						transaction_id IN (:transaction_ids)', [
				'transaction_ids' => $db->escapeArray($trans_ids),
			]);
		}

		$this->forwardTo->go();
	}

}
