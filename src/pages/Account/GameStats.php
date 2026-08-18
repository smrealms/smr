<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Alliance;
use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Player;
use Smr\Race;
use Smr\Rankings;
use Smr\Template;

class GameStats extends AccountPage {

	public function __construct(
		private readonly int $gameID,
	) {}

	public function build(Account $account, Template $template): void {
		//get game id
		$gameID = $this->gameID;

		$statsGame = Game::getGame($gameID);

		$template->pageTopic = 'Game Stats: ' . $statsGame->getName() . ' (' . $gameID . ')';

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT count(*) total_players, IFNULL(MAX(experience),0) max_exp, IFNULL(MAX(alignment),0) max_align, IFNULL(MIN(alignment),0) min_align, IFNULL(MAX(kills),0) max_kills FROM player WHERE game_id = :game_id', [
			'game_id' => $gameID,
		]);
		$dbRecord = $dbResult->record();

		// Handle details about when to provide a linked alliance name
		$getAllianceLink = function(Alliance $alliance) use ($statsGame): string {
			if ($statsGame->hasEnded()) {
				// If game has ended, offer a link to alliance roster details
				$allianceName = create_link(
					new PreviousGameAllianceDetail($alliance->getGameID(), $alliance->getAllianceID()),
					$alliance->getAllianceDisplayName(includeAllianceID: true),
				);
			} else {
				$allianceName = $alliance->getAllianceDisplayName();
			}
			return $allianceName;
		};

		// Get current account's player for this game (if any)
		try {
			$player = Player::getPlayer($account->getAccountID(), $gameID);
		} catch (PlayerNotFound) {
			$player = null;
		}

		$playerExpRecords = Rankings::playerStats('experience', $gameID, 10);
		$playerExpRanks = Rankings::collectRankings($playerExpRecords, $player);

		$playerKillRecords = Rankings::playerStats('kills', $gameID, 10);
		$playerKillRanks = Rankings::collectRankings($playerKillRecords, $player);

		$allianceTopTen = function(string $stat) use ($getAllianceLink, $gameID, $player): array {
			$allianceRecords = Rankings::allianceStats($stat, $gameID, 10);
			$allianceRanks = Rankings::collectAllianceRankings($allianceRecords, $player);
			foreach ($allianceRanks as $rank => $info) {
				$allianceRanks[$rank]['AllianceName'] = $getAllianceLink($info['Alliance']);
			}
			return $allianceRanks;
		};

		if ($player !== null) {
			$playerInfo = [
				'Name' => $player->getLevelName() . ' ' . $player->getDisplayName(),
				'Race' => Race::getName($player->getRaceID()),
				'Alliance' => (
					$player->hasAlliance() ?
					$getAllianceLink($player->getAlliance()) :
					$player->getAllianceDisplayName()
				),
				'Experience' => number_format($player->getExperience()),
				'Kills' => number_format($player->getKills()),
				'Hall Of Fame' => create_link($player->getPersonalHofHREF(), 'View'),
				'News' => create_link(
					new NewsReadAdvanced(
						gameID: $gameID,
						submit: 'Search For Player',
						accountIDs: [$player->getAccountID()],
					),
					'View',
				),
			];
		} else {
			$playerInfo = null;
		}

		$template->pageRenderer = fn() => GameStatsRenderer::render(
			StatsGame: $statsGame,
			TotalPlayers: $dbRecord->getInt('total_players'),
			HighestExp: $dbRecord->getInt('max_exp'),
			HighestAlign: $dbRecord->getInt('max_align'),
			LowestAlign: $dbRecord->getInt('min_align'),
			HighestKills: $dbRecord->getInt('max_kills'),
			TotalAlliances: $db->count('alliance', ['game_id' => $gameID]),
			ExperienceRankings: $playerExpRanks,
			KillRankings: $playerKillRanks,
			AllianceExpRankings: $allianceTopTen('experience'),
			AllianceKillRankings: $allianceTopTen('kills'),
			PlayerInfo: $playerInfo,
			BackHref: new GamePlay()->href(),
			ThisAccount: $account,
		);
	}

}
