<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\Alliance;
use Smr\Database;
use Smr\Epoch;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Session;
use Smr\Template;

class AllianceBank extends PlayerPage {

	public function __construct(
		private readonly int $allianceID,
	) {}

	public function build(Player $player, Template $template): void {
		$session = Session::getInstance();

		// is account validated?
		if (!$player->getAccount()->isValidated()) {
			create_error('You are not validated so you cannot use banks.');
		}

		$allianceID = $this->allianceID;

		$alliance = Alliance::getAlliance($allianceID, $player->getGameID());
		$template->pageTopic = 'Bank';

		Menu::bank();

		$db = Database::getInstance();
		$dbResult = $db->read(
			'SELECT * FROM alliance_treaties WHERE game_id = :game_id
					AND (alliance_id_1 = :alliance_id OR alliance_id_2 = :alliance_id)
					AND aa_access = 1 AND official = \'TRUE\'',
			$player->getAlliance()->SQLID,
		);
		$alliedAllianceBanks = [];
		foreach ($dbResult->records() as $dbRecord) {
			$alliedAllianceBanks[$dbRecord->getInt('alliance_id_2')] = Alliance::getAlliance($dbRecord->getInt('alliance_id_2'), $alliance->getGameID());
			$alliedAllianceBanks[$dbRecord->getInt('alliance_id_1')] = Alliance::getAlliance($dbRecord->getInt('alliance_id_1'), $alliance->getGameID());
		}

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

		$table = 'alliance_has_roles';
		if ($alliance->getAllianceID() === $player->getAllianceID()) {
			$roleID = $player->getAllianceRole($alliance->getAllianceID());
			$dbResult = $db->select($table, [...$alliance->SQLID, 'role_id' => $roleID]);
		} else {
			$role = $player->getAlliance()->getAllianceName();
			$dbResult = $db->select($table, [...$alliance->SQLID, 'role' => $role]);
		}
		$dbRecord = $dbResult->record();
		$canExempt = $dbRecord->getBoolean('exempt_with');
		$withdrawalPerDay = $dbRecord->getInt('with_per_day');

		$positiveWithdrawal = null;
		$unlimitedWithdrawal = false;
		$remainingWithdrawal = null;
		$totalWithdrawn = null;
		if ($dbRecord->getBoolean('positive_balance')) {
			$positiveWithdrawal = $withdrawalPerDay + $playerTrans['Deposit'] - $playerTrans['Payment'];
		} elseif ($withdrawalPerDay === ALLIANCE_BANK_UNLIMITED) {
			$unlimitedWithdrawal = true;
		} else {
			$dbResult = $db->read('SELECT IFNULL(sum(amount), 0) as total FROM alliance_bank_transactions
						WHERE ' . Alliance::SQL . '
						AND payee_id = :payee_id AND transaction = \'Payment\' AND exempt = 0
						AND time > :one_day_ago', [
				...$alliance->SQLID,
				'payee_id' => $db->escapeNumber($player->getAccountID()),
				'one_day_ago' => $db->escapeNumber(Epoch::time() - 86400),
			]);
			$totalWithdrawn = $dbResult->record()->getInt('total');
			$remainingWithdrawal = $withdrawalPerDay - $totalWithdrawn;
			$totalWithdrawn = $totalWithdrawn;
		}

		$maxValue = $session->getRequestVarInt('maxValue', 0);
		$minValue = $session->getRequestVarInt('minValue', 0);

		// By default, display the last 5 records
		if ($maxValue <= 0) {
			$maxValue = $db->count('alliance_bank_transactions', $alliance->SQLID);
		}

		if ($minValue <= 0 || $minValue > $maxValue) {
			$minValue = max(1, $maxValue - 4);
		}

		$query = 'SELECT *
			FROM alliance_bank_transactions
			WHERE ' . Alliance::SQL . '
			LIMIT :limit_offset, :limit_count';
		$dbResult = $db->read($query, [
			...$alliance->SQLID,
			'limit_offset' => $minValue - 1,
			'limit_count' => $maxValue - $minValue + 1,
		]);

		$bankTransactions = [];
		$transactionIDs = [];
		foreach ($dbResult->records() as $i => $dbRecord) {
			$index = $i + $minValue;
			$trans = $dbRecord->getString('transaction');
			$bankTransactions[$index] = [
				'Time' => $dbRecord->getInt('time'),
				'Player' => Player::getPlayer($dbRecord->getInt('payee_id'), $player->getGameID()),
				'Reason' => $dbRecord->getString('reason'),
				'TransactionType' => $trans,
				'Withdrawal' => $trans === 'Payment' ? number_format($dbRecord->getInt('amount')) : '',
				'Deposit' => $trans === 'Deposit' ? number_format($dbRecord->getInt('amount')) : '',
				'Exempt' => $dbRecord->getInt('exempt') === 1,
			];
			$transactionIDs[] = $dbRecord->getInt('transaction_id');
		}

		// only if we have at least one result
		if (count($bankTransactions) > 0) {
			$filterTransactionsFormHREF = new self($allianceID)->href();
			$exemptTransactionsFormHREF = new AllianceBankExemptProcessor($this, $transactionIDs)->href();
		} else {
			$filterTransactionsFormHREF = null;
			$exemptTransactionsFormHREF = null;
		}

		$template->pageRenderer = fn() => AllianceBankRenderer::render(
			AlliedAllianceBanks: $alliedAllianceBanks,
			CanExempt: $canExempt,
			PositiveWithdrawal: $positiveWithdrawal,
			UnlimitedWithdrawal: $unlimitedWithdrawal,
			WithdrawalPerDay: $withdrawalPerDay,
			RemainingWithdrawal: $remainingWithdrawal,
			TotalWithdrawn: $totalWithdrawn,
			BankTransactions: $bankTransactions,
			MinValue: $minValue,
			MaxValue: $maxValue,
			FilterTransactionsFormHREF: $filterTransactionsFormHREF,
			ExemptTransactionsFormHREF: $exemptTransactionsFormHREF,
			EndingBalance: number_format($alliance->getBank()),
			BankReportHREF: new AllianceBankReport($allianceID)->href(),
			BankTransactionForm: new AllianceBankProcessor($allianceID),
			ThisAccount: $player->getAccount(),
			ThisPlayer: $player,
		);
	}

}
