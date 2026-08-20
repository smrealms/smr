<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Exception;
use Smr\Database;
use Smr\Epoch;
use Smr\Html\Submit;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class VotingCenterProcessor extends PlayerPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionRelationsIncrease;
	public readonly Submit $actionRelationsDecrease;
	public readonly Submit $actionTreatyYes;
	public readonly Submit $actionTreatyNo;

	public function __construct(
		private readonly int $otherRaceID,
	) {
		// Action values are database inputs and must not change
		$this->actionRelationsIncrease = new Submit(self::ACTION, 'INC');
		$this->actionRelationsDecrease = new Submit(self::ACTION, 'DEC');
		$this->actionTreatyYes = new Submit(self::ACTION, 'YES');
		$this->actionTreatyNo = new Submit(self::ACTION, 'NO');
	}

	public function build(Player $player): never {
		$db = Database::getInstance();

		if (!$player->isOnCouncil()) {
			create_error('You have to be on the council in order to vote.');
		}

		$action = Request::get(self::ACTION);

		$race_id = $this->otherRaceID;

		if ($action === $this->actionRelationsIncrease->value || $action === $this->actionRelationsDecrease->value) {
			$db->replace('player_votes_relation', [
				'account_id' => $player->getAccountID(),
				'game_id' => $player->getGameID(),
				'race_id_1' => $player->getRaceID(),
				'race_id_2' => $race_id,
				'action' => $action,
				'time' => Epoch::time(),
			]);
		} elseif ($action === $this->actionTreatyYes->value || $action === $this->actionTreatyNo->value) {
			$db->replace('player_votes_pact', [
				'account_id' => $player->getAccountID(),
				'game_id' => $player->getGameID(),
				'race_id_1' => $player->getRaceID(),
				'race_id_2' => $race_id,
				'vote' => $action,
			]);
		} else {
			throw new Exception('Unexpected action: ' . $action);
		}

		new VotingCenter()->go();
	}

}
