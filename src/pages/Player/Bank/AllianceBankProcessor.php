<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AllianceBankProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $allianceID
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();

		$alliance_id = $this->allianceID;

		$amount = Request::getInt('amount');

		// no negative amounts are allowed
		if ($amount <= 0) {
			create_error('You must actually enter an amount > 0!');
		}
		$message = Request::get('message');
		if (empty($message)) {
			$message = 'No reason specified';
		}

		$alliance = Alliance::getAlliance($alliance_id, $player->getGameID());
		$action = Request::get('action');
		if ($action == 'Deposit') {
			if ($player->getCredits() < $amount) {
				create_error('You don\'t own that much money!');
			}

			$amount = $alliance->increaseBank($amount); // handles overflow
			$player->decreaseCredits($amount);
			if ($alliance->getBank() >= MAX_MONEY) {
				$message .= ' (Account is Full)';
			}

		} else {
			$action = 'Payment';
			if ($alliance->getBank() < $amount) {
				create_error('Your alliance isn\'t that rich!');
			}
			$query = '';
			if ($alliance_id == $player->getAllianceID()) {
				$role_id = $player->getAllianceRole($alliance_id);
				$query = 'role_id = ' . $db->escapeNumber($role_id);
			} else {
				// Alliance treaties create new roles with alliance names
				$query = 'role = ' . $db->escapeString($player->getAlliance()->getAllianceName());
			}
			$dbResult = $db->read('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND ' . $query);
			$dbRecord = $dbResult->record();
			$withdrawalPerDay = $dbRecord->getInt('with_per_day');
			if ($dbRecord->getBoolean('positive_balance')) {
				$dbResult = $db->read('SELECT transaction, sum(amount) as total FROM alliance_bank_transactions
					WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . '
					GROUP BY transaction');
				$playerTrans = ['Deposit' => 0, 'Payment' => 0];
				foreach ($dbResult->records() as $dbRecord) {
					$playerTrans[$dbRecord->getString('transaction')] = $dbRecord->getInt('total');
				}
				$allowedWithdrawal = $withdrawalPerDay + $playerTrans['Deposit'] - $playerTrans['Payment'];
				if ($allowedWithdrawal - $amount < 0) {
					create_error('Your alliance won\'t allow you to take so much with how little you\'ve given!');
				}
			} elseif ($withdrawalPerDay >= 0) {
				$dbResult = $db->read('SELECT IFNULL(sum(amount), 0) as total FROM alliance_bank_transactions
							WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . '
								AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
								AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . '
								AND transaction = \'Payment\'
								AND exempt = 0
								AND time > ' . $db->escapeNumber(Epoch::time() - 86400));
				$total = $dbResult->record()->getInt('total');
				if ($total + $amount > $withdrawalPerDay) {
					create_error('Your alliance doesn\'t allow you to take that much cash this often!');
				}
			}

			$amount = $player->increaseCredits($amount); // handles overflow
			$alliance->decreaseBank($amount);
		}

		// log action
		$player->log(LOG_TYPE_BANK, $action . ' ' . $amount . ' credits for alliance account of ' . $alliance->getAllianceName());

		// get next transaction id
		$dbResult = $db->read('SELECT IFNULL(MAX(transaction_id), 0) as max_id FROM alliance_bank_transactions
					WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . '
						AND game_id = ' . $db->escapeNumber($player->getGameID()));
		$next_id = $dbResult->record()->getInt('max_id') + 1;

		// save log
		$requestExempt = Request::has('requestExempt') ? 1 : 0;
		$db->insert('alliance_bank_transactions', [
			'alliance_id' => $db->escapeNumber($alliance_id),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'transaction_id' => $db->escapeNumber($next_id),
			'time' => $db->escapeNumber(Epoch::time()),
			'payee_id' => $db->escapeNumber($player->getAccountID()),
			'reason' => $db->escapeString($message),
			'transaction' => $db->escapeString($action),
			'amount' => $db->escapeNumber($amount),
			'request_exempt' => $db->escapeNumber($requestExempt),
		]);

		// update player credits
		$player->update();

		// save money for alliance
		$alliance->update();

		$container = new AllianceBank($alliance_id);
		$container->go();
	}

}
