<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class AccountEditSearchProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		$db = Database::getInstance();

		$account_id = Request::getInt('account_id');
		$player_name = Request::get('player_name');
		$searchGameID = Request::getInt('game_id');

		if (!empty($player_name)) {
			$gameIDClause = $searchGameID != 0 ? ' AND game_id = ' . $db->escapeNumber($searchGameID) . ' ' : '';
			$dbResult = $db->read('SELECT account_id FROM player
							WHERE player_name = ' . $db->escapeString($player_name) . $gameIDClause . '
							ORDER BY game_id DESC LIMIT 1');
			if ($dbResult->hasRecord()) {
				$account_id = $dbResult->record()->getInt('account_id');
			} else {
				$dbResult = $db->read('SELECT * FROM player
								WHERE player_name LIKE ' . $db->escapeString($player_name . '%') . $gameIDClause . ' LIMIT 1');
				if ($dbResult->hasRecord()) {
					$account_id = $dbResult->record()->getInt('account_id');
				}
			}
		}

		// get account from db
		$dbResult = $db->read('SELECT account_id FROM account WHERE account_id = ' . $db->escapeNumber($account_id) . ' OR ' .
											   'login LIKE ' . $db->escapeString(Request::get('login')) . ' OR ' .
											   'email LIKE ' . $db->escapeString(Request::get('email')) . ' OR ' .
											   'hof_name LIKE ' . $db->escapeString(Request::get('hofname')) . ' OR ' .
											   'validation_code LIKE ' . $db->escapeString(Request::get('val_code')) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			$container = new AccountEdit($dbResult->record()->getInt('account_id'));
		} else {
			$errorMsg = 'No matching accounts were found!';
			$container = new AccountEditSearch(errorMessage: $errorMsg);
		}
		$container->go();
	}

}
