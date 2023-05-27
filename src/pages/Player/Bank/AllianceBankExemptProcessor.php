<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\AllianceExemptAuthorize;
use Smr\Request;

class AllianceBankExemptProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly ?int $minTransactionID = null,
		private readonly ?int $maxTransactionID = null,
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();

		//only if we are coming from the bank screen do we unexempt selection first
		if ($this->minTransactionID !== null && $this->maxTransactionID !== null) {
			$db->write('UPDATE alliance_bank_transactions SET exempt = 0 WHERE game_id = :game_id AND alliance_id = :alliance_id
						AND transaction_id BETWEEN :transaction_id_min AND :transaction_id_max', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'alliance_id' => $db->escapeNumber($player->getAllianceID()),
				'transaction_id_min' => $db->escapeNumber($this->minTransactionID),
				'transaction_id_max' => $db->escapeNumber($this->maxTransactionID),
			]);
		}

		if (Request::has('exempt')) {
			$trans_ids = array_keys(Request::getArray('exempt'));
			$db->write('UPDATE alliance_bank_transactions SET exempt = 1, request_exempt = 0 WHERE game_id = :game_id AND alliance_id = :alliance_id
						AND transaction_id IN (:transaction_ids)', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'alliance_id' => $db->escapeNumber($player->getAllianceID()),
				'transaction_ids' => $db->escapeArray($trans_ids),
			]);
		}

		if ($this->minTransactionID !== null) {
			$container = new AllianceBank($player->getAllianceID());
		} else {
			$container = new AllianceExemptAuthorize();
		}
		$container->go();
	}

}
