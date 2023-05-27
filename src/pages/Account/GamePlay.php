<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Game;
use Smr\Globals;
use Smr\Page\AccountPage;
use Smr\Pages\Account\HistoryGames\ExtendedStats;
use Smr\Pages\Account\HistoryGames\GameNews;
use Smr\Pages\Account\HistoryGames\HallOfFame;
use Smr\Pages\Account\HistoryGames\Summary;
use Smr\Player;
use Smr\Template;

class GamePlay extends AccountPage {

	public string $file = 'game_play.php';

	public function __construct(
		private readonly ?string $message = null,
		private readonly ?string $errorMessage = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Play Game');

		$template->assign('ErrorMessage', $this->errorMessage);
		$template->assign('Message', $this->message);

		$template->assign('UserRankingLink', (new UserRankingView())->href());
		$template->assign('UserRankName', $account->getRank()->name);

		// ***************************************
		// ** Play Games
		// ***************************************

		$games = [];
		$games['Play'] = [];
		$game_id_list = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT end_time, game_id, game_name, game_speed, game_type
					FROM game JOIN player USING (game_id)
					WHERE account_id = :account_id
						AND enabled = \'TRUE\'
						AND end_time >= :now
					ORDER BY start_time, game_id DESC', [
			'account_id' => $db->escapeNumber($account->getAccountID()),
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$game_id = $dbRecord->getInt('game_id');
			$games['Play'][$game_id]['ID'] = $game_id;
			$games['Play'][$game_id]['Name'] = $dbRecord->getString('game_name');
			$games['Play'][$game_id]['Type'] = Game::GAME_TYPES[$dbRecord->getInt('game_type')];
			$games['Play'][$game_id]['EndDate'] = date($account->getDateTimeFormatSplit(), $dbRecord->getInt('end_time'));
			$games['Play'][$game_id]['Speed'] = $dbRecord->getFloat('game_speed');

			$container = new GamePlayProcessor($game_id);
			$games['Play'][$game_id]['PlayGameLink'] = $container->href();

			// creates a new player object
			$curr_player = Player::getPlayer($account->getAccountID(), $game_id);

			// update turns for this game
			$curr_player->updateTurns();

			// generate list of game_id that this player is joined
			$game_id_list[] = $game_id;

			$result2 = $db->read('SELECT count(*) as num_playing
							FROM player
							WHERE last_cpl_action >= :inactive_time
								AND game_id = :game_id', [
				'inactive_time' => $db->escapeNumber(Epoch::time() - TIME_BEFORE_INACTIVE),
				'game_id' => $db->escapeNumber($game_id),
			]);
			$games['Play'][$game_id]['NumberPlaying'] = $result2->record()->getInt('num_playing');

			// create a container that will hold next url and additional variables.

			$container_game = new GameStats($game_id);
			$games['Play'][$game_id]['GameStatsLink'] = $container_game->href();
			$games['Play'][$game_id]['Turns'] = $curr_player->getTurns();
			$games['Play'][$game_id]['LastMovement'] = format_time(Epoch::time() - $curr_player->getLastActive(), true);
		}

		if (count($games['Play']) === 0) {
			unset($games['Play']);
		}

		// ***************************************
		// ** Join Games
		// ***************************************

		if (count($game_id_list) > 0) {
			$dbResult = $db->read('SELECT game_id
						FROM game
						WHERE game_id NOT IN (:game_ids)
							AND end_time >= :now
							AND enabled = :enabled
						ORDER BY start_time DESC', [
				'now' => $db->escapeNumber(Epoch::time()),
				'enabled' => $db->escapeBoolean(true),
				'game_ids' => $db->escapeArray($game_id_list),
			]);
		} else {
			$dbResult = $db->read('SELECT game_id
						FROM game
						WHERE end_time >= :now
							AND enabled = :enabled
						ORDER BY start_time DESC', [
				'now' => $db->escapeNumber(Epoch::time()),
				'enabled' => $db->escapeBoolean(true),
			]);
		}

		// are there any results?
		foreach ($dbResult->records() as $dbRecord) {
			$game_id = $dbRecord->getInt('game_id');
			$game = Game::getGame($game_id);
			$games['Join'][$game_id] = [
				'ID' => $game_id,
				'Name' => $game->getName(),
				'JoinTime' => $game->getJoinTime(),
				'StartDate' => date($account->getDateTimeFormatSplit(), $game->getStartTime()),
				'EndDate' => date($account->getDateTimeFormatSplit(), $game->getEndTime()),
				'Players' => $game->getTotalPlayers(),
				'Type' => $game->getGameType(),
				'Speed' => $game->getGameSpeed(),
				'Credits' => $game->getCreditsNeeded(),
			];
			// create a container that will hold next url and additional variables.
			$container = new GameJoin($game_id);

			$games['Join'][$game_id]['JoinGameLink'] = $container->href();
		}

		// ***************************************
		// ** Previous Games
		// ***************************************

		$games['Previous'] = [];

		//New previous games
		$dbResult = $db->read('SELECT start_time, end_time, game_name, game_type, game_speed, game_id ' .
				'FROM game WHERE enabled = \'TRUE\' AND end_time < :now ORDER BY game_id DESC', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$game_id = $dbRecord->getInt('game_id');
			$games['Previous'][$game_id]['ID'] = $game_id;
			$games['Previous'][$game_id]['Name'] = $dbRecord->getString('game_name');
			$games['Previous'][$game_id]['StartDate'] = date($account->getDateFormat(), $dbRecord->getInt('start_time'));
			$games['Previous'][$game_id]['EndDate'] = date($account->getDateFormat(), $dbRecord->getInt('end_time'));
			$games['Previous'][$game_id]['Type'] = Game::GAME_TYPES[$dbRecord->getInt('game_type')];
			$games['Previous'][$game_id]['Speed'] = $dbRecord->getFloat('game_speed');
			// create a container that will hold next url and additional variables.
			$container = new HallOfFameAll($game_id);
			$games['Previous'][$game_id]['PreviousGameHOFLink'] = $container->href();
			$container = new NewsReadArchives($game_id);
			$games['Previous'][$game_id]['PreviousGameNewsLink'] = $container->href();
			$container = new GameStats($game_id);
			$games['Previous'][$game_id]['PreviousGameLink'] = $container->href();
		}

		foreach (Globals::getHistoryDatabases() as $databaseName => $oldColumn) {
			//Old previous games
			$db->switchDatabases($databaseName);
			$dbResult = $db->read('SELECT start_date, end_date, game_name, type, speed, game_id
								FROM game ORDER BY game_id DESC');
			foreach ($dbResult->records() as $dbRecord) {
				$game_id = $dbRecord->getInt('game_id');
				$index = $databaseName . $game_id;
				$gameName = $dbRecord->getString('game_name');
				$games['Previous'][$index]['ID'] = $game_id;
				$games['Previous'][$index]['Name'] = $gameName;
				$games['Previous'][$index]['StartDate'] = date($account->getDateFormat(), $dbRecord->getInt('start_date'));
				$games['Previous'][$index]['EndDate'] = date($account->getDateFormat(), $dbRecord->getInt('end_date'));
				$games['Previous'][$index]['Type'] = $dbRecord->getString('type');
				$games['Previous'][$index]['Speed'] = $dbRecord->getFloat('speed');
				// create a container that will hold next url and additional variables.
				$container = new Summary($databaseName, $game_id, $gameName);
				$games['Previous'][$index]['PreviousGameLink'] = $container->href();
				$container = new HallOfFame($databaseName, $game_id, $gameName);
				$games['Previous'][$index]['PreviousGameHOFLink'] = $container->href();
				$container = new GameNews($databaseName, $game_id, $gameName);
				$games['Previous'][$index]['PreviousGameNewsLink'] = $container->href();
				$container = new ExtendedStats($databaseName, $game_id, $gameName);
				$games['Previous'][$index]['PreviousGameStatsLink'] = $container->href();
			}
		}
		$db->switchDatabaseToLive(); // restore database

		$template->assign('Games', $games);

		// ***************************************
		// ** Voting
		// ***************************************
		$container = new Vote();
		$template->assign('VotingHref', $container->href());

		$dbResult = $db->read('SELECT * FROM voting WHERE end > :now ORDER BY end DESC', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		if ($dbResult->hasRecord()) {
			$votedFor = [];
			$dbResult2 = $db->read('SELECT * FROM voting_results WHERE account_id = :account_id', [
				'account_id' => $db->escapeNumber($account->getAccountID()),
			]);
			foreach ($dbResult2->records() as $dbRecord2) {
				$votedFor[$dbRecord2->getInt('vote_id')] = $dbRecord2->getInt('option_id');
			}
			$voting = [];
			foreach ($dbResult->records() as $dbRecord) {
				$voteID = $dbRecord->getInt('vote_id');
				$voting[$voteID]['ID'] = $voteID;
				$container = new VoteProcessor($voteID, new self());
				$voting[$voteID]['HREF'] = $container->href();
				$voting[$voteID]['Question'] = $dbRecord->getString('question');
				$voting[$voteID]['TimeRemaining'] = format_time($dbRecord->getInt('end') - Epoch::time(), true);
				$voting[$voteID]['Options'] = [];
				$dbResult2 = $db->read('SELECT option_id,text,count(account_id) FROM voting_options LEFT OUTER JOIN voting_results USING(vote_id,option_id) WHERE vote_id = :vote_id GROUP BY option_id', [
					'vote_id' => $db->escapeNumber($dbRecord->getInt('vote_id')),
				]);
				foreach ($dbResult2->records() as $dbRecord2) {
					$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['ID'] = $dbRecord2->getInt('option_id');
					$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['Text'] = $dbRecord2->getString('text');
					$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['Chosen'] = isset($votedFor[$dbRecord->getInt('vote_id')]) && $votedFor[$voteID] === $dbRecord2->getInt('option_id');
					$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['Votes'] = $dbRecord2->getInt('count(account_id)');
				}
			}
			$template->assign('Voting', $voting);
		}

		// ***************************************
		// ** Announcements View
		// ***************************************
		$container = new LoginAnnouncements(viewAll: true);
		$template->assign('OldAnnouncementsLink', $container->href());
	}

}
