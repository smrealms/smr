<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class NewsReadAdvancedProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
	) {}

	public function build(Account $account): never {
		$submit = Request::get('submit');

		$db = Database::getInstance();
		if ($submit == 'Search For Player') {
			$playerName = Request::get('playerName');
			$dbResult = $db->read('SELECT account_id FROM player WHERE player_name LIKE :player_name_like AND game_id = :game_id', [
				'player_name_like' => $db->escapeString('%' . $playerName . '%'),
				'game_id' => $db->escapeNumber($this->gameID),
			]);
			$IDs = [];
			foreach ($dbResult->records() as $dbRecord) {
				$IDs[] = $dbRecord->getInt('account_id');
			}
			$container = new NewsReadAdvanced($this->gameID, $submit, label: $playerName, accountIDs: $IDs);
		} elseif ($submit == 'Search For Players') {
			$playerName1 = Request::get('player1');
			$playerName2 = Request::get('player2');
			$dbResult = $db->read('SELECT account_id FROM player WHERE (player_name LIKE :player_name_like_1 OR player_name LIKE :player_name_like_2) AND game_id = :game_id', [
				'player_name_like_1' => $db->escapeString('%' . $playerName1 . '%'),
				'player_name_like_2' => $db->escapeString('%' . $playerName2 . '%'),
				'game_id' => $db->escapeNumber($this->gameID),
			]);
			$IDs = [];
			foreach ($dbResult->records() as $dbRecord) {
				$IDs[] = $dbRecord->getInt('account_id');
			}
			$label = $playerName1 . ' vs. ' . $playerName2;
			$container = new NewsReadAdvanced($this->gameID, $submit, label: $label, accountIDs: $IDs);
		} elseif ($submit == 'Search For Alliance') {
			$allianceID = Request::getInt('allianceID');
			$container = new NewsReadAdvanced($this->gameID, $submit, allianceIDs: [$allianceID]);
		} elseif ($submit == 'Search For Alliances') {
			$allianceID1 = Request::getInt('alliance1');
			$allianceID2 = Request::getInt('alliance2');
			$container = new NewsReadAdvanced($this->gameID, $submit, allianceIDs: [$allianceID1, $allianceID2]);
		} else {
			throw new Exception('Unknown submit: ' . $submit);
		}

		$container->go();
	}

}
