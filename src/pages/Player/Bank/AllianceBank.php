<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Epoch;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Session;
use Smr\Template;

class AllianceBank extends PlayerPage {

	public string $file = 'bank_alliance.php';

	public function __construct(
		private readonly int $allianceID,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$session = Session::getInstance();

		// is account validated?
		if (!$player->getAccount()->isValidated()) {
			create_error('You are not validated so you cannot use banks.');
		}

		$allianceID = $this->allianceID;

		$alliance = Alliance::getAlliance($allianceID, $player->getGameID());
		$template->assign('PageTopic', 'Bank');

		Menu::bank();

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM alliance_treaties WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND (alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')
					AND aa_access = 1 AND official = \'TRUE\'');
		$alliedAllianceBanks = [];
		foreach ($dbResult->records() as $dbRecord) {
			$alliedAllianceBanks[$dbRecord->getInt('alliance_id_2')] = Alliance::getAlliance($dbRecord->getInt('alliance_id_2'), $alliance->getGameID());
			$alliedAllianceBanks[$dbRecord->getInt('alliance_id_1')] = Alliance::getAlliance($dbRecord->getInt('alliance_id_1'), $alliance->getGameID());
		}
		$template->assign('AlliedAllianceBanks', $alliedAllianceBanks);

		$dbResult = $db->read('SELECT transaction, sum(amount) as total FROM alliance_bank_transactions
					WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . '
					GROUP BY transaction');
		$playerTrans = ['Deposit' => 0, 'Payment' => 0];
		foreach ($dbResult->records() as $dbRecord) {
			$playerTrans[$dbRecord->getString('transaction')] = $dbRecord->getInt('total');
		}

		if ($alliance->getAllianceID() == $player->getAllianceID()) {
			$role_id = $player->getAllianceRole($alliance->getAllianceID());
			$query = 'role_id = ' . $db->escapeNumber($role_id);
		} else {
			$query = 'role = ' . $db->escapeString($player->getAlliance()->getAllianceName());
		}

		$dbResult = $db->read('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND ' . $query);
		$dbRecord = $dbResult->record();
		$template->assign('CanExempt', $dbRecord->getBoolean('exempt_with'));
		$withdrawalPerDay = $dbRecord->getInt('with_per_day');

		if ($dbRecord->getBoolean('positive_balance')) {
			$template->assign('PositiveWithdrawal', $withdrawalPerDay + $playerTrans['Deposit'] - $playerTrans['Payment']);
		} elseif ($withdrawalPerDay == ALLIANCE_BANK_UNLIMITED) {
			$template->assign('UnlimitedWithdrawal', true);
		} else {
			$dbResult = $db->read('SELECT IFNULL(sum(amount), 0) as total FROM alliance_bank_transactions WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
						AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND transaction = \'Payment\' AND exempt = 0 AND time > ' . $db->escapeNumber(Epoch::time() - 86400));
			$totalWithdrawn = $dbResult->record()->getInt('total');
			$template->assign('WithdrawalPerDay', $withdrawalPerDay);
			$template->assign('RemainingWithdrawal', $withdrawalPerDay - $totalWithdrawn);
			$template->assign('TotalWithdrawn', $totalWithdrawn);
		}

		$maxValue = $session->getRequestVarInt('maxValue', 0);
		$minValue = $session->getRequestVarInt('minValue', 0);

		if ($maxValue <= 0) {
			$dbResult = $db->read('SELECT IFNULL(MAX(transaction_id), 0) as max_transaction_id FROM alliance_bank_transactions
						WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
						AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()));
			$maxValue = $dbResult->record()->getInt('max_transaction_id');
		}

		if ($minValue <= 0 || $minValue > $maxValue) {
			$minValue = max(1, $maxValue - 5);
		}

		$query = 'SELECT time, transaction_id, transaction, amount, exempt, reason, payee_id
			FROM alliance_bank_transactions
			WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
			AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID());

		$query .= ' AND transaction_id>=' . $db->escapeNumber($minValue) . '
					AND transaction_id<=' . $db->escapeNumber($maxValue) . '
					ORDER BY time LIMIT ' . (1 + $maxValue - $minValue);

		$dbResult = $db->read($query);

		// only if we have at least one result
		if ($dbResult->hasRecord()) {
			$bankTransactions = [];
			foreach ($dbResult->records() as $dbRecord) {
				$trans = $dbRecord->getString('transaction');
				$bankTransactions[$dbRecord->getInt('transaction_id')] = [
					'Time' => $dbRecord->getInt('time'),
					'Player' => Player::getPlayer($dbRecord->getInt('payee_id'), $player->getGameID()),
					'Reason' => $dbRecord->getString('reason'),
					'TransactionType' => $trans,
					'Withdrawal' => $trans == 'Payment' ? number_format($dbRecord->getInt('amount')) : '',
					'Deposit' => $trans == 'Deposit' ? number_format($dbRecord->getInt('amount')) : '',
					'Exempt' => $dbRecord->getInt('exempt') == 1,
				];
			}
			$template->assign('BankTransactions', $bankTransactions);

			$template->assign('MinValue', $minValue);
			$template->assign('MaxValue', $maxValue);
			$container = new self($allianceID);
			$template->assign('FilterTransactionsFormHREF', $container->href());

			$container = new AllianceBankExemptProcessor($minValue, $maxValue);
			$template->assign('ExemptTransactionsFormHREF', $container->href());

			$template->assign('EndingBalance', number_format($alliance->getBank()));
		}

		$container = new AllianceBankReport($allianceID);
		$template->assign('BankReportHREF', $container->href());

		$container = new AllianceBankProcessor($allianceID);
		$template->assign('BankTransactionFormHREF', $container->href());
	}

}
