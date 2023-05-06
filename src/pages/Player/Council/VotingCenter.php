<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Globals;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Race;
use Smr\Template;

class VotingCenter extends PlayerPage {

	use ReusableTrait;

	public string $file = 'council_vote.php';

	public function build(AbstractPlayer $player, Template $template): void {
		if (!$player->isOnCouncil()) {
			create_error('You have to be on the council in order to vote.');
		}

		$template->assign('PageTopic', 'Ruling Council Of ' . $player->getRaceName());
		Menu::council($player->getRaceID());

		// determine for what we voted
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM player_votes_relation
					WHERE account_id = :account_id
						AND game_id = :game_id', [
			'account_id' => $db->escapeNumber($player->getAccountID()),
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$votedForRace = null;
		$votedFor = null;
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$votedForRace = $dbRecord->getInt('race_id_2');
			$votedFor = $dbRecord->getString('action');
		}

		$voteRelations = [];
		$raceRelations = Globals::getRaceRelations($player->getGameID(), $player->getRaceID());
		foreach (Race::getPlayableIDs() as $raceID) {
			if ($raceID === $player->getRaceID()) {
				continue;
			}
			$container = new VotingCenterProcessor($raceID);
			$voteRelations[$raceID] = [
				'HREF' => $container->href(),
				'Increased' => $votedForRace === $raceID && $votedFor === 'INC',
				'Decreased' => $votedForRace === $raceID && $votedFor === 'DEC',
				'Relations' => $raceRelations[$raceID],
			];
		}
		$template->assign('VoteRelations', $voteRelations);

		$voteTreaties = [];
		$dbResult = $db->read('SELECT * FROM race_has_voting
					WHERE :now < end_time
					AND game_id = :game_id
					AND race_id_1 = :race_id_1', [
			'now' => $db->escapeNumber(Epoch::time()),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'race_id_1' => $db->escapeNumber($player->getRaceID()),
		]);

		foreach ($dbResult->records() as $dbRecord) {
			$otherRaceID = $dbRecord->getInt('race_id_2');
			$container = new VotingCenterProcessor($otherRaceID);

			// get 'yes' votes
			$dbResult2 = $db->read('SELECT count(*) FROM player_votes_pact
						WHERE game_id = :game_id
							AND race_id_1 = :race_id_1
							AND race_id_2 = :race_id_2
							AND vote = \'YES\'', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'race_id_1' => $db->escapeNumber($player->getRaceID()),
				'race_id_2' => $db->escapeNumber($otherRaceID),
			]);
			$yesVotes = $dbResult2->record()->getInt('count(*)');

			// get 'no' votes
			$dbResult2 = $db->read('SELECT count(*) FROM player_votes_pact
						WHERE game_id = :game_id
							AND race_id_1 = :race_id_1
							AND race_id_2 = :race_id_2
							AND vote = \'NO\'', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'race_id_1' => $db->escapeNumber($player->getRaceID()),
				'race_id_2' => $db->escapeNumber($otherRaceID),
			]);
			$noVotes = $dbResult2->record()->getInt('count(*)');

			$dbResult2 = $db->read('SELECT vote FROM player_votes_pact
						WHERE account_id = :account_id
							AND game_id = :game_id
							AND race_id_1 = :race_id_1
							AND race_id_2 = :race_id_2', [
				'account_id' => $db->escapeNumber($player->getAccountID()),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'race_id_1' => $db->escapeNumber($player->getRaceID()),
				'race_id_2' => $db->escapeNumber($otherRaceID),
			]);
			$votedFor = '';
			if ($dbResult2->hasRecord()) {
				$votedFor = $dbResult2->record()->getString('vote'); // this should be a boolean
			}

			$voteTreaties[$otherRaceID] = [
				'HREF' => $container->href(),
				'Type' => $dbRecord->getString('type'),
				'EndTime' => $dbRecord->getInt('end_time'),
				'For' => $votedFor === 'YES',
				'Against' => $votedFor === 'NO',
				'NoVotes' => $noVotes,
				'YesVotes' => $yesVotes,
			];
		}
		$template->assign('VoteTreaties', $voteTreaties);
	}

}
