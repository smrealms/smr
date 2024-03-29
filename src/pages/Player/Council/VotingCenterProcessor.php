<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class VotingCenterProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $otherRaceID,
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();

		if (!$player->isOnCouncil()) {
			create_error('You have to be on the council in order to vote.');
		}

		$action = strtoupper(Request::get('action'));

		if ($action === 'INCREASE') {
			$action = 'INC';
		} elseif ($action === 'DECREASE') {
			$action = 'DEC';
		}

		$race_id = $this->otherRaceID;

		if ($action === 'INC' || $action === 'DEC') {
			$db->replace('player_votes_relation', [
				'account_id' => $player->getAccountID(),
				'game_id' => $player->getGameID(),
				'race_id_1' => $player->getRaceID(),
				'race_id_2' => $race_id,
				'action' => $action,
				'time' => Epoch::time(),
			]);
		} elseif ($action === 'YES' || $action === 'NO') {
			$db->replace('player_votes_pact', [
				'account_id' => $player->getAccountID(),
				'game_id' => $player->getGameID(),
				'race_id_1' => $player->getRaceID(),
				'race_id_2' => $race_id,
				'vote' => $action,
			]);
		} elseif ($action === 'VETO') {
			// try to cancel both votings
			$sqlParams = [
				'game_id' => $player->getGameID(),
				'race_id_1' => $player->getRaceID(),
				'race_id_2' => $race_id,
			];
			$db->delete('race_has_voting', $sqlParams);
			$db->delete('player_votes_pact', $sqlParams);
			$sqlParams2 = [
				'game_id' => $player->getGameID(),
				'race_id_1' => $race_id,
				'race_id_2' => $player->getRaceID(),
			];
			$db->delete('race_has_voting', $sqlParams2);
			$db->delete('player_votes_pact', $sqlParams2);
		}

		(new VotingCenter())->go();
	}

}
