<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class VotingCenterProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $otherRaceID
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();

		if (!$player->isOnCouncil()) {
			create_error('You have to be on the council in order to vote.');
		}

		$action = strtoupper(Request::get('action'));

		if ($action == 'INCREASE') {
			$action = 'INC';
		} elseif ($action == 'DECREASE') {
			$action = 'DEC';
		}

		$race_id = $this->otherRaceID;

		if ($action == 'INC' || $action == 'DEC') {
			$db->replace('player_votes_relation', [
				'account_id' => $db->escapeNumber($player->getAccountID()),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'race_id_1' => $db->escapeNumber($player->getRaceID()),
				'race_id_2' => $db->escapeNumber($race_id),
				'action' => $db->escapeString($action),
				'time' => $db->escapeNumber(Epoch::time()),
			]);
		} elseif ($action == 'YES' || $action == 'NO') {
			$db->replace('player_votes_pact', [
				'account_id' => $db->escapeNumber($player->getAccountID()),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'race_id_1' => $db->escapeNumber($player->getRaceID()),
				'race_id_2' => $db->escapeNumber($race_id),
				'vote' => $db->escapeString($action),
			]);
		} elseif ($action == 'VETO') {
			// try to cancel both votings
			$sqlParams = [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'race_id_1' => $db->escapeNumber($player->getRaceID()),
				'race_id_2' => $db->escapeNumber($race_id),
			];
			$db->write('DELETE FROM race_has_voting
					WHERE game_id = :game_id
						AND race_id_1 = :race_id_1
						AND race_id_2 = :race_id_2', $sqlParams);
			$db->write('DELETE FROM player_votes_pact
					WHERE game_id = :game_id
						AND race_id_1 = :race_id_1
						AND race_id_2 = :race_id_2', $sqlParams);
			$db->write('DELETE FROM race_has_voting
					WHERE game_id = :game_id
						AND race_id_1 = :race_id_2
						AND race_id_2 = :race_id_1', $sqlParams);
			$db->write('DELETE FROM player_votes_pact
					WHERE game_id = :game_id
						AND race_id_1 = :race_id_2
						AND race_id_2 = :race_id_1', $sqlParams);
		}

		(new VotingCenter())->go();
	}

}
