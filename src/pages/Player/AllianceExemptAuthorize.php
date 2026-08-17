<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Pages\Player\Bank\AllianceBankExemptProcessor;
use Smr\Player;
use Smr\Template;

class AllianceExemptAuthorize extends PlayerPage {

	public function build(Player $player, Template $template): void {
		$alliance = $player->getAlliance();

		$template->pageTopic = $alliance->getAllianceDisplayName(false, true);
		Menu::alliance($alliance->getAllianceID());

		//get rid of already approved entries
		$db = Database::getInstance();
		$db->update(
			'alliance_bank_transactions',
			['request_exempt' => 0],
			['exempt' => 1],
		);

		$dbResult = $db->select('alliance_bank_transactions', [
			'request_exempt' => 1,
			'exempt' => 0,
			...$alliance->SQLID,
		]);
		$transactions = [];
		if ($dbResult->hasRecord()) {
			foreach ($dbResult->records() as $dbRecord) {
				$recPlayer = Player::getPlayer($dbRecord->getInt('payee_id'), $player->getGameID());
				$transactions[] = [
					'type' => $dbRecord->getString('transaction') === 'Payment' ? 'Withdraw' : 'Deposit',
					'player' => $recPlayer->getDisplayName(),
					'reason' => $dbRecord->getString('reason'),
					'amount' => number_format($dbRecord->getInt('amount')),
					'transactionID' => $dbRecord->getInt('transaction_id'),
				];
			}
		}
		$template->pageRenderer = fn() => AllianceExemptAuthorizeRenderer::render(
			Transactions: $transactions,
			ExemptHREF: new AllianceBankExemptProcessor($this)->href(),
		);
	}

}
