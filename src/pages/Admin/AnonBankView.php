<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Session;
use Smr\Template;

class AnonBankView extends AccountPage {

	public string $file = 'admin/anon_acc_view.php';

	public function build(Account $account, Template $template): void {
		$session = Session::getInstance();

		//view anon acct activity.
		$template->assign('PageTopic', 'View Anonymous Account Info');

		$container = new AnonBankViewSelect();
		$template->assign('BackHREF', $container->href());

		$anonID = $session->getRequestVarInt('anon_account');
		$gameID = $session->getRequestVarInt('view_game_id');

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT *
					FROM anon_bank_transactions
					JOIN player USING(account_id, game_id)
					WHERE anon_id = :anon_id
						AND game_id = :game_id
					ORDER BY transaction_id', [
			'anon_id' => $db->escapeNumber($anonID),
			'game_id' => $db->escapeNumber($gameID),
		]);
		$rows = [];
		foreach ($dbResult->records() as $dbRecord) {
			$rows[] = [
				'player_name' => $dbRecord->getString('player_name'),
				'transaction' => $dbRecord->getString('transaction'),
				'amount' => $dbRecord->getInt('amount'),
			];
		}
		if (!$rows) {
			$message = '<p><span class="red">Anon account #' . $anonID . ' in Game ' . $gameID . ' does NOT exist!</span></p>';
			$container = new AnonBankViewSelect($message);
			$container->go();
		}
		$template->assign('Rows', $rows);
		$template->assign('AnonID', $anonID);
		$template->assign('ViewGameID', $gameID);
	}

}
