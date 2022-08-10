<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use AbstractSmrPlayer;
use Menu;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Session;
use Smr\Template;
use SmrPlayer;

class AnonBankDetail extends PlayerPage {

	public string $file = 'bank_anon_detail.php';

	public function __construct(
		private readonly int $anonBankID,
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$session = Session::getInstance();

		$account_num = $this->anonBankID;
		$maxValue = $session->getRequestVarInt('maxValue', 0);
		$minValue = $session->getRequestVarInt('minValue', 0);

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT *
					FROM anon_bank
					WHERE anon_id=' . $db->escapeNumber($account_num) . '
					AND game_id=' . $db->escapeNumber($player->getGameID()));
		$dbRecord = $dbResult->record();

		$balance = $dbRecord->getInt('amount');
		$template->assign('Balance', $balance);

		if ($maxValue <= 0) {
			$dbResult = $db->read('SELECT IFNULL(MAX(transaction_id), 5) as max_transaction_id FROM anon_bank_transactions
						WHERE game_id=' . $db->escapeNumber($player->getGameID()) . '
						AND anon_id=' . $db->escapeNumber($account_num));
			$maxValue = $dbResult->record()->getInt('max_transaction_id');
		}

		if ($minValue <= 0 || $minValue >= $maxValue) {
			$minValue = max(1, $maxValue - 5);
		}

		$query = 'SELECT *
					FROM player
					JOIN anon_bank_transactions USING (game_id, account_id)
					WHERE player.game_id=' . $db->escapeNumber($player->getGameID()) . '
					AND anon_bank_transactions.anon_id=' . $db->escapeNumber($account_num);

		if ($maxValue > 0) {
			$query .= ' AND transaction_id>=' . $db->escapeNumber($minValue) . '
						AND transaction_id<=' . $db->escapeNumber($maxValue) . '
						ORDER BY time LIMIT ' . (1 + $maxValue - $minValue);
		} else {
			$query .= ' ORDER BY time LIMIT 10';
		}

		$dbResult = $db->read($query);

		// only if we have at least one result
		if ($dbResult->hasRecord()) {
			$template->assign('MinValue', $minValue);
			$template->assign('MaxValue', $maxValue);
			$container = new self($account_num);
			$template->assign('ShowHREF', $container->href());

			$transactions = [];
			foreach ($dbResult->records() as $dbRecord) {
				$transactionPlayer = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
				$transaction = $dbRecord->getString('transaction');
				$amount = number_format($dbRecord->getInt('amount'));
				$transactions[$dbRecord->getInt('transaction_id')] = [
					'date' => date($player->getAccount()->getDateTimeFormatSplit(), $dbRecord->getInt('time')),
					'payment' => $transaction == 'Payment' ? $amount : '',
					'deposit' => $transaction == 'Deposit' ? $amount : '',
					'link' => $transactionPlayer->getLinkedDisplayName(),
				];
			}
			$template->assign('Transactions', $transactions);
		}

		$container = new AnonBankDetailProcessor($account_num);
		$template->assign('TransactionHREF', $container->href());

		$template->assign('PageTopic', 'Anonymous Account #' . $account_num);
		Menu::bank();
	}

}
