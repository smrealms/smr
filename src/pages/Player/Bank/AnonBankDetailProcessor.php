<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Exception;
use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AnonBankDetailProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $anonBankID,
	) {}

	public function build(AbstractPlayer $player): never {
		$action = Request::get('action');
		if (!in_array($action, ['Deposit', 'Payment'], true)) {
			throw new Exception('Invalid action submitted: ' . $action);
		}

		$amount = Request::getInt('amount');
		$account_num = $this->anonBankID;
		// no negative amounts are allowed
		if ($amount <= 0) {
			create_error('You must actually enter an amount > 0!');
		}

		// Get the next transaction ID for this anon bank
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT IFNULL(MAX(transaction_id), 0) AS max_id FROM anon_bank_transactions WHERE game_id = :game_id AND anon_id = :anon_id', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'anon_id' => $db->escapeNumber($account_num),
		]);
		$trans_id = $dbResult->record()->getInt('max_id') + 1;

		// Get the amount currently in this anon bank
		$dbResult = $db->read('SELECT amount FROM anon_bank WHERE anon_id = :anon_id AND game_id = :game_id', [
			'anon_id' => $db->escapeNumber($account_num),
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$anonAmount = $dbResult->record()->getInt('amount');

		// Update the credit amounts for the player and the bank
		if ($action === 'Deposit') {
			if ($player->getCredits() < $amount) {
				create_error('You don\'t own that much money!');
			}
			$amount = min($amount, MAX_MONEY - $anonAmount); // handle overflow
			if ($amount === 0) {
				create_error('This account has reached the maximum credit limit!');
			}

			$player->decreaseCredits($amount);
			$db->write('UPDATE anon_bank SET amount = amount + :amount WHERE game_id = :game_id AND anon_id = :anon_id', [
				'amount' => $db->escapeNumber($amount),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'anon_id' => $db->escapeNumber($account_num),
			]);
		} else {
			if ($anonAmount < $amount) {
				create_error('You don\'t have that much money on your account!');
			}

			$amount = $player->increaseCredits($amount); // handles overflow
			$db->write('UPDATE anon_bank SET amount = amount - :amount WHERE game_id = :game_id AND anon_id = :anon_id', [
				'amount' => $db->escapeNumber($amount),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'anon_id' => $db->escapeNumber($account_num),
			]);
		}

		$player->update();

		// Log the bank transaction
		$db->insert('anon_bank_transactions', [
			'account_id' => $player->getAccountID(),
			'game_id' => $player->getGameID(),
			'anon_id' => $account_num,
			'transaction_id' => $trans_id,
			'transaction' => $action,
			'amount' => $amount,
			'time' => Epoch::time(),
		]);

		// Log the player action
		$player->log(LOG_TYPE_BANK, $action . ' of ' . $amount . ' credits in anonymous account #' . $account_num);

		$container = new AnonBankDetail($account_num);
		$container->go();
	}

}
