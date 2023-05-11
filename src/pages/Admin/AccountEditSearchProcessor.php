<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class AccountEditSearchProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$db = Database::getInstance();

		$account_id = Request::getInt('account_id');
		$player_name = Request::get('player_name');
		$searchGameID = Request::getInt('game_id');

		if ($player_name !== '') {
			$gameIDClause = 'AND (:game_id = 0 OR :game_id = game_id)';
			$dbResult = $db->read('SELECT account_id FROM player
							WHERE player_name = :player_name ' . $gameIDClause . '
							ORDER BY game_id DESC LIMIT 1', [
				'player_name' => $db->escapeString($player_name),
				'game_id' => $db->escapeNumber($searchGameID),
			]);
			if ($dbResult->hasRecord()) {
				$account_id = $dbResult->record()->getInt('account_id');
			} else {
				$dbResult = $db->read('SELECT * FROM player
								WHERE player_name LIKE :player_name_like ' . $gameIDClause . ' LIMIT 1', [
					'player_name_like' => $db->escapeString($player_name . '%'),
					'game_id' => $db->escapeNumber($searchGameID),
				]);
				if ($dbResult->hasRecord()) {
					$account_id = $dbResult->record()->getInt('account_id');
				}
			}
		}

		// get account from db
		$dbResult = $db->read('SELECT account_id FROM account WHERE account_id = :account_id OR
								login LIKE :login_like OR
								email LIKE :email_like OR
								hof_name LIKE :hof_name_like OR
								validation_code LIKE :validation_code_like LIMIT 1', [
			'account_id' => $db->escapeNumber($account_id),
			'login_like' => $db->escapeString(Request::get('login')),
			'email_like' => $db->escapeString(Request::get('email')),
			'hof_name_like' => $db->escapeString(Request::get('hofname')),
			'validation_code_like' => $db->escapeString(Request::get('val_code')),
		]);
		if ($dbResult->hasRecord()) {
			$container = new AccountEdit($dbResult->record()->getInt('account_id'));
		} else {
			$errorMsg = 'No matching accounts were found!';
			$container = new AccountEditSearch(errorMessage: $errorMsg);
		}
		$container->go();
	}

}
