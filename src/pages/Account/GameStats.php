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

	public string $file = 'game_stats.php';

	public function __construct(
		private readonly int $gameID,
	) {}

	public function build(Account $account, Template $template): void {
		//get game id
		$gameID = $this->gameID;

		$statsGame = Game::getGame($gameID);
		$template->assign('StatsGame', $statsGame);

		$template->assign('PageTopic', 'Game Stats: ' . $statsGame->getName() . ' (' . $gameID . ')');

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT count(*) total_players, IFNULL(MAX(experience),0) max_exp, IFNULL(MAX(alignment),0) max_align, IFNULL(MIN(alignment),0) min_align, IFNULL(MAX(kills),0) max_kills FROM player WHERE game_id = :game_id', [
			'game_id' => $gameID,
		]);
		$dbRecord = $dbResult->record();
		$template->assign('TotalPlayers', $dbRecord->getInt('total_players'));
		$template->assign('HighestExp', $dbRecord->getInt('max_exp'));
		$template->assign('HighestAlign', $dbRecord->getInt('max_align'));
		$template->assign('LowestAlign', $dbRecord->getInt('min_align'));
		$template->assign('HighestKills', $dbRecord->getInt('max_kills'));

		$template->assign('TotalAlliances', $db->count('alliance', ['game_id' => $gameID]));

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
		$template->assign('ExperienceRankings', $playerExpRanks);

		$playerKillRecords = Rankings::playerStats('kills', $gameID, 10);
		$playerKillRanks = Rankings::collectRankings($playerKillRecords, $player);
		$template->assign('KillRankings', $playerKillRanks);

		$allianceTopTen = function(string $stat) use ($getAllianceLink, $gameID, $player): array {
			$allianceRecords = Rankings::allianceStats($stat, $gameID, 10);
			$allianceRanks = Rankings::collectAllianceRankings($allianceRecords, $player);
			foreach ($allianceRanks as $rank => $info) {
				$allianceRanks[$rank]['AllianceName'] = $getAllianceLink($info['Alliance']);
			}
			return $allianceRanks;
		};
		$template->assign('AllianceExpRankings', $allianceTopTen('experience'));
		$template->assign('AllianceKillRankings', $allianceTopTen('kills'));

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
		$template->assign('PlayerInfo', $playerInfo);

		$template->assign('BackHref', (new GamePlay())->href());
	}

}
