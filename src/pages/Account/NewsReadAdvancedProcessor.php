<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class NewsReadAdvancedProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public const ACTION_PLAYER = 'player';
	public const ACTION_PLAYERS = 'players';
	public const ACTION_ALLIANCE = 'alliance';
	public const ACTION_ALLIANCES = 'alliances';

	public readonly Submit $actionSearchPlayer;
	public readonly Submit $actionSearchPlayers;
	public readonly Submit $actionSearchAlliance;
	public readonly Submit $actionSearchAlliances;

	public function __construct(
		private readonly int $gameID,
	) {
		$this->actionSearchPlayer = new Submit(self::ACTION, self::ACTION_PLAYER);
		$this->actionSearchPlayers = new Submit(self::ACTION, self::ACTION_PLAYERS);
		$this->actionSearchAlliance = new Submit(self::ACTION, self::ACTION_ALLIANCE);
		$this->actionSearchAlliances = new Submit(self::ACTION, self::ACTION_ALLIANCES);
	}

	public function build(Account $account): never {
		$submit = Request::get(self::ACTION);

		$db = Database::getInstance();
		if ($submit === self::ACTION_PLAYER) {
			$playerName = Request::get('playerName');
			$dbResult = $db->read('SELECT player_id FROM player WHERE player_name LIKE :player_name_like AND game_id = :game_id', [
				'player_name_like' => $db->escapeString('%' . $playerName . '%'),
				'game_id' => $db->escapeNumber($this->gameID),
			]);
			$IDs = [];
			foreach ($dbResult->records() as $dbRecord) {
				$IDs[] = $dbRecord->getInt('player_id');
			}
			$container = new NewsReadAdvanced($this->gameID, $submit, label: $playerName, playerIDs: $IDs);
		} elseif ($submit === self::ACTION_PLAYERS) {
			$playerName1 = Request::get('player1');
			$playerName2 = Request::get('player2');
			$dbResult = $db->read('SELECT player_id FROM player WHERE (player_name LIKE :player_name_like_1 OR player_name LIKE :player_name_like_2) AND game_id = :game_id', [
				'player_name_like_1' => $db->escapeString('%' . $playerName1 . '%'),
				'player_name_like_2' => $db->escapeString('%' . $playerName2 . '%'),
				'game_id' => $db->escapeNumber($this->gameID),
			]);
			$IDs = [];
			foreach ($dbResult->records() as $dbRecord) {
				$IDs[] = $dbRecord->getInt('player_id');
			}
			$label = $playerName1 . ' vs. ' . $playerName2;
			$container = new NewsReadAdvanced($this->gameID, $submit, label: $label, playerIDs: $IDs);
		} elseif ($submit === self::ACTION_ALLIANCE) {
			$allianceID = Request::getInt('allianceID');
			$container = new NewsReadAdvanced($this->gameID, $submit, allianceIDs: [$allianceID]);
		} elseif ($submit === self::ACTION_ALLIANCES) {
			$allianceID1 = Request::getInt('alliance1');
			$allianceID2 = Request::getInt('alliance2');
			$container = new NewsReadAdvanced($this->gameID, $submit, allianceIDs: [$allianceID1, $allianceID2]);
		} else {
			throw new Exception('Unknown submit: ' . $submit);
		}

		$container->go();
	}

}
