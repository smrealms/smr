<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\AllianceExemptAuthorize;
use Smr\Request;

class AllianceBankExemptProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly ?int $minTransactionID = null,
		private readonly ?int $maxTransactionID = null
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$db = Database::getInstance();

		//only if we are coming from the bank screen do we unexempt selection first
		if ($this->minTransactionID !== null && $this->maxTransactionID !== null) {
			$db->write('UPDATE alliance_bank_transactions SET exempt = 0 WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
						AND transaction_id BETWEEN ' . $db->escapeNumber($this->minTransactionID) . ' AND ' . $db->escapeNumber($this->maxTransactionID));
		}

		if (Request::has('exempt')) {
			$trans_ids = array_keys(Request::getArray('exempt'));
			$db->write('UPDATE alliance_bank_transactions SET exempt = 1, request_exempt = 0 WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
						AND transaction_id IN (' . $db->escapeArray($trans_ids) . ')');
		}

		if ($this->minTransactionID !== null) {
			$container = new AllianceBank($player->getAllianceID());
		} else {
			$container = new AllianceExemptAuthorize();
		}
		$container->go();
	}

}
