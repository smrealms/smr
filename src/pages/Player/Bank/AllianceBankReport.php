<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class AllianceBankReport extends PlayerPage {

	public string $file = 'bank_report.php';

	private const WITHDRAW = 0;
	private const DEPOSIT = 1;

	public function __construct(
		private readonly int $allianceID,
		private readonly bool $reportSent = false,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$alliance_id = $this->allianceID;

		//get all transactions
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM alliance_bank_transactions WHERE alliance_id = :alliance_id AND game_id = :game_id', [
			'alliance_id' => $db->escapeNumber($alliance_id),
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		if (!$dbResult->hasRecord()) {
			create_error('Your alliance has no recorded transactions.');
		}
		$trans = [];
		foreach ($dbResult->records() as $dbRecord) {
			$transType = ($dbRecord->getString('transaction') === 'Payment') ? self::WITHDRAW : self::DEPOSIT;
			$payeeId = ($dbRecord->getInt('exempt')) ? 0 : $dbRecord->getInt('payee_id');
			// initialize payee if necessary
			if (!isset($trans[$payeeId])) {
				$trans[$payeeId] = [self::WITHDRAW => 0, self::DEPOSIT => 0];
			}
			$trans[$payeeId][$transType] += $dbRecord->getInt('amount');
		}

		//ordering
		$playerIDs = array_keys($trans);
		$totals = [];
		foreach ($trans as $accId => $transArray) {
			$totals[$accId] = $transArray[self::DEPOSIT] - $transArray[self::WITHDRAW];
		}
		arsort($totals, SORT_NUMERIC);
		$dbResult = $db->read('SELECT * FROM player WHERE account_id IN (:account_ids) AND game_id = :game_id ORDER BY player_name', [
			'account_ids' => $db->escapeArray($playerIDs),
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$players = [0 => 'Alliance Funds'];
		foreach ($dbResult->records() as $dbRecord) {
			$players[$dbRecord->getInt('account_id')] = htmlentities($dbRecord->getString('player_name'));
		}

		//format it this way so its easy to send to the alliance MB if requested.
		$text = '<table class="nobord centered" cellspacing="1">';
		$text .= '<tr><th>Player</th><th>Deposits</th><th>Withdrawals</th><th>Total</th></tr>';
		$balance = 0;
		foreach ($totals as $accId => $total) {
			$balance += $total;
			$text .= '<tr>';
			$text .= '<td><span class="yellow">' . $players[$accId] . '</span></td>';
			$text .= '<td class="right">' . number_format($trans[$accId][self::DEPOSIT]) . '</td>';
			$text .= '<td class="right">-' . number_format($trans[$accId][self::WITHDRAW]) . '</td>';
			$text .= '<td class="right"><span class="';
			if ($total < 0) {
				$text .= 'red bold';
			} else {
				$text .= 'bold';
			}
			$text .= '">' . number_format($total) . '</span></td>';
			$text .= '</tr>';
		}
		$text .= '</table>';
		$text = '<div class="center"><br />Ending Balance: ' . number_format($balance) . '</div><br />' . $text;
		$template->assign('BankReport', $text);

		if (!$this->reportSent) {
			$container = new AllianceBankReportProcessor($alliance_id, $text);
			$template->assign('SendReportHREF', $container->href());
		}

		$template->assign('PageTopic', 'Alliance Bank Report');
		Menu::bank();
	}

}
