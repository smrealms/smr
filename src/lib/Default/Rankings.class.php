<?php declare(strict_types=1);

class Rankings {

	/**
	 * @param array<int, Smr\DatabaseRecord> $rankedStats
	 */
	public static function collectSectorRankings(array $rankedStats, AbstractSmrPlayer $player, int $minRank = 1, int $maxRank = 10) : array {
		$rankedStats = self::filterRanks($rankedStats, $minRank, $maxRank);
		$currRank = $minRank;

		$rankings = [];
		foreach ($rankedStats as $sectorID => $dbRecord) {
			if ($player->getSectorID() == $sectorID) {
				$class = ' class="bold"';
			} else {
				$class = '';
			}

			$rankings[$currRank++] = [
				'Class' => $class,
				'SectorID' => $sectorID,
				'Value' => $dbRecord->getInt('amount'),
			];
		}
		return $rankings;
	}

	/**
	 * @param array<int, Smr\DatabaseRecord> $rankedStats
	 */
	public static function collectRaceRankings(array $rankedStats, AbstractSmrPlayer $player) : array {
		$currRank = 1;
		$rankings = [];
		foreach ($rankedStats as $raceID => $dbRecord) {
			if ($player->getRaceID() == $raceID) {
				$style = ' class="bold"';
			} else {
				$style = '';
			}

			$rankings[$currRank++] = [
				'style' => $style,
				'race_id' => $raceID,
				'amount' => $dbRecord->getInt('amount'),
				'amount_avg' => IRound($dbRecord->getInt('amount') / $dbRecord->getInt('num_players')),
				'num_players' => $dbRecord->getInt('num_players'),
			];
		}
		return $rankings;
	}

	/**
	 * @param array<int, Smr\DatabaseRecord> $rankedStats
	 */
	public static function collectAllianceRankings(array $rankedStats, AbstractSmrPlayer $player, int $minRank = 1, $maxRank = 10) : array {
		$rankedStats = self::filterRanks($rankedStats, $minRank, $maxRank);
		$currRank = $minRank;

		$rankings = array();
		foreach ($rankedStats as $allianceID => $dbRecord) {
			$currentAlliance = SmrAlliance::getAlliance($allianceID, $player->getGameID());

			$class = '';
			if ($player->getAllianceID() == $currentAlliance->getAllianceID()) {
				$class = ' class="bold"';
			} elseif ($currentAlliance->hasDisbanded()) {
				$class = ' class="red"';
			}

			$rankings[$currRank++] = array(
				'Alliance' => $currentAlliance,
				'Class' => $class,
				'Value' => $dbRecord->getInt('amount'),
			);
		}
		return $rankings;
	}

	/**
	 * @param array<int, Smr\DatabaseRecord> $rankedStats
	 */
	public static function collectRankings(array $rankedStats, AbstractSmrPlayer $player, int $minRank = 1, $maxRank = 10) : array {
		$rankedStats = self::filterRanks($rankedStats, $minRank, $maxRank);
		$currRank = $minRank;

		$rankings = array();
		foreach ($rankedStats as $dbRecord) {
			$currentPlayer = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);

			$class = '';
			if ($player->equals($currentPlayer)) {
				$class .= 'bold';
			}
			if ($currentPlayer->hasNewbieStatus()) {
				$class .= ' newbie';
			}
			if ($class != '') {
				$class = ' class="' . trim($class) . '"';
			}

			$rankings[$currRank++] = array(
				'Player' => $currentPlayer,
				'Class' => $class,
				'Value' => $dbRecord->getInt('amount'),
			);
		}
		return $rankings;
	}

	private static function filterRanks(array $rankedStats, int $minRank, int $maxRank) : array {
		$offset = $minRank - 1;
		$length = $maxRank - $offset;
		return array_slice($rankedStats, $offset, $length, preserve_keys: true);
	}

	/**
	 * Get stats from the player table grouped by race and sorted by $stat (high to low).
	 *
	 * @return array<int, Smr\DatabaseRecord>
	 */
	public static function raceStats(string $stat, int $gameID) : array {
		$db = Smr\Database::getInstance();
		$raceStats = [];
		$dbResult = $db->read('SELECT race_id, COALESCE(SUM(' . $stat . '), 0) as amount, count(*) as num_players FROM player WHERE game_id = ' . $db->escapeNumber($gameID) . ' GROUP BY race_id ORDER BY amount DESC');
		foreach ($dbResult->records() as $dbRecord) {
			$raceStats[$dbRecord->getInt('race_id')] = $dbRecord;
		}
		return $raceStats;
	}

	/**
	 * Get stats from the player table sorted by $stat (high to low).
	 *
	 * @return array<int, Smr\DatabaseRecord>
	 */
	public static function playerStats(string $stat, int $gameID) : array {
		$db = Smr\Database::getInstance();
		$playerStats = [];
		$dbResult = $db->read('SELECT player.*, ' . $stat . ' AS amount FROM player WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY amount DESC, player_name');
		foreach ($dbResult->records() as $dbRecord) {
			$playerStats[$dbRecord->getInt('player_id')] = $dbRecord;
		}
		return $playerStats;
	}

	/**
	 * Gets player stats from the hof table sorted by $category (high to low).
	 *
	 * @return array<int, Smr\DatabaseRecord>
	 */
	public static function playerStatsFromHOF(array $category, int $gameID) : array {
		$db = Smr\Database::getInstance();
		$playerStats = [];
		$dbResult = $db->read('SELECT p.*, COALESCE(ph.amount,0) amount FROM player p LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $db->escapeArray($category, ':', false) . ' WHERE p.game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY amount DESC, player_name');
		foreach ($dbResult->records() as $dbRecord) {
			$playerStats[$dbRecord->getInt('player_id')] = $dbRecord;
		}
		return $playerStats;
	}

	/**
	 * Get stats from the alliance table sorted by $stat (high to low).
	 *
	 * @return array<int, Smr\DatabaseRecord>
	 */
	public static function allianceStats(string $stat, int $gameID) : array {
		$db = Smr\Database::getInstance();
		$allianceStats = [];
		$dbResult = $db->read('SELECT alliance_id, alliance_' . $stat . ' AS amount FROM alliance WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY amount DESC, alliance_name');
		foreach ($dbResult->records() as $dbRecord) {
			$allianceStats[$dbRecord->getInt('alliance_id')] = $dbRecord;
		}
		return $allianceStats;
	}

	/**
	 * Given a $rankedStats array returned by one of the stats functions,
	 * find the rank associated with a specific ID.
	 */
	public static function ourRank(array $rankedStats, int $ourID) : int {
		return array_search($ourID, array_keys($rankedStats)) + 1;
	}

	public static function calculateMinMaxRanks(int $ourRank, int $totalRanks) : array {
		$session = Smr\Session::getInstance();
		$minRank = $session->getRequestVarInt('min_rank', $ourRank - 5);
		$maxRank = $session->getRequestVarInt('max_rank', $ourRank + 5);

		if ($minRank <= 0 || $minRank > $totalRanks) {
			$minRank = 1;
		}

		$maxRank = min($maxRank, $totalRanks);

		$template = Smr\Template::getInstance();
		$template->assign('MinRank', $minRank);
		$template->assign('MaxRank', $maxRank);
		$template->assign('TotalRanks', $totalRanks);

		return [$minRank, $maxRank];
	}
}
