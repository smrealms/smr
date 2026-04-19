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
		private readonly int $allianceID,
	) {}

	public function build(AbstractPlayer $player): never {
		$amount = Request::getInt('amount');

		// no negative amounts are allowed
		if ($amount <= 0) {
			create_error('You must actually enter an amount > 0!');
		}
		$message = Request::get('message');
		if ($message === '') {
			$message = 'No reason specified';
		}

		$alliance = Alliance::getAlliance($this->allianceID, $player->getGameID());

		$action = Request::get('action');
		if ($action !== 'Deposit') {
			$action = 'Payment';
		}

		self::doTransaction(
			action: $action,
			alliance: $alliance,
			player: $player,
			message: $message,
			amount: $amount,
			requestExempt: Request::has('requestExempt'),
		);

		$container = new AllianceBank($this->allianceID);
		$container->go();
	}

	/**
	 * @param 'Deposit'|'Payment' $action
	 */
	public static function doTransaction(
		string $action,
		Alliance $alliance,
		AbstractPlayer $player,
		string $message,
		int $amount,
		bool $requestExempt = false,
	): void {
		$db = Database::getInstance();
		if ($action === 'Deposit') {
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

			$table = 'alliance_has_roles';
			if ($alliance->getAllianceID() === $player->getAllianceID()) {
				$roleID = $player->getAllianceRole($alliance->getAllianceID());
				$dbResult = $db->select($table, [...$alliance->SQLID, 'role_id' => $roleID]);
			} else {
				// Alliance treaties create new roles with alliance names
				$role = $player->getAlliance()->getAllianceName();
				$dbResult = $db->select($table, [...$alliance->SQLID, 'role' => $role]);
			}
			$dbRecord = $dbResult->record();
			$withdrawalPerDay = $dbRecord->getInt('with_per_day');
			if ($dbRecord->getBoolean('positive_balance')) {
				$dbResult = $db->read('SELECT transaction, sum(amount) as total FROM alliance_bank_transactions
					WHERE ' . Alliance::SQL . ' AND payee_id = :payee_id
					GROUP BY transaction', [
					...$alliance->SQLID,
					'payee_id' => $db->escapeNumber($player->getAccountID()),
				]);
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
							WHERE ' . Alliance::SQL . '
								AND payee_id = :payee_id
								AND transaction = \'Payment\'
								AND exempt = 0
								AND time > :one_day_ago', [
					...$alliance->SQLID,
					'payee_id' => $db->escapeNumber($player->getAccountID()),
					'one_day_ago' => $db->escapeNumber(Epoch::time() - 86400),
				]);
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

		// save transaction
		$db->insert('alliance_bank_transactions', [
			...$alliance->SQLID,
			'time' => Epoch::time(),
			'payee_id' => $player->getAccountID(),
			'reason' => $message,
			'transaction' => $action,
			'amount' => $amount,
			'request_exempt' => $requestExempt ? 1 : 0,
		]);
	}

}
