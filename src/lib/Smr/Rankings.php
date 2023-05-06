<?php declare(strict_types=1);

namespace Smr;

class Rankings {

	/**
	 * @param array<int, \Smr\DatabaseRecord> $rankedStats
	 * @return array<int, array{Class: string, SectorID: int, Value: int}>
	 */
	public static function collectSectorRankings(array $rankedStats, AbstractPlayer $player, int $minRank = 1, int $maxRank = 10): array {
		$rankedStats = self::filterRanks($rankedStats, $minRank, $maxRank);
		$currRank = $minRank;

		$rankings = [];
		foreach ($rankedStats as $sectorID => $dbRecord) {
			if ($player->getSectorID() === $sectorID) {
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
	 * @param array<int, \Smr\DatabaseRecord> $rankedStats
	 * @return array<int, array{style: string, race_id: int, amount: int, amount_avg: int, num_players: int}>
	 */
	public static function collectRaceRankings(array $rankedStats, AbstractPlayer $player): array {
		$currRank = 1;
		$rankings = [];
		foreach ($rankedStats as $raceID => $dbRecord) {
			if ($player->getRaceID() === $raceID) {
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
	 * @param array<int, \Smr\DatabaseRecord> $rankedStats
	 * @return array<int, array{Alliance: \Smr\Alliance, Class: string, Value: int}>
	 */
	public static function collectAllianceRankings(array $rankedStats, ?AbstractPlayer $player, int $minRank = 1, int $maxRank = 10): array {
		$rankedStats = self::filterRanks($rankedStats, $minRank, $maxRank);
		$currRank = $minRank;

		$rankings = [];
		foreach ($rankedStats as $allianceID => $dbRecord) {
			$currentAlliance = Alliance::getAlliance($allianceID, $dbRecord->getInt('game_id'), false, $dbRecord);

			$class = '';
			if ($player !== null && $player->getAllianceID() === $currentAlliance->getAllianceID()) {
				$class = ' class="bold"';
			} elseif ($currentAlliance->hasDisbanded()) {
				$class = ' class="red"';
			}

			$rankings[$currRank++] = [
				'Alliance' => $currentAlliance,
				'Class' => $class,
				'Value' => $dbRecord->getInt('amount'),
			];
		}
		return $rankings;
	}

	/**
	 * @param array<int, \Smr\DatabaseRecord> $rankedStats
	 * @return array<int, array{Player: \Smr\AbstractPlayer, Class: string, Value: int}>
	 */
	public static function collectRankings(array $rankedStats, ?AbstractPlayer $player, int $minRank = 1, int $maxRank = 10): array {
		$rankedStats = self::filterRanks($rankedStats, $minRank, $maxRank);
		$currRank = $minRank;

		$rankings = [];
		foreach ($rankedStats as $dbRecord) {
			$currentPlayer = Player::getPlayer($dbRecord->getInt('account_id'), $dbRecord->getInt('game_id'), false, $dbRecord);

			$class = '';
			if ($player !== null && $player->equals($currentPlayer)) {
				$class .= 'bold';
			}
			if ($currentPlayer->hasNewbieStatus()) {
				$class .= ' newbie';
			}
			if ($class !== '') {
				$class = ' class="' . trim($class) . '"';
			}

			$rankings[$currRank++] = [
				'Player' => $currentPlayer,
				'Class' => $class,
				'Value' => $dbRecord->getInt('amount'),
			];
		}
		return $rankings;
	}

	/**
	 * @param array<int, \Smr\DatabaseRecord> $rankedStats
	 * @return array<int, \Smr\DatabaseRecord>
	 */
	private static function filterRanks(array $rankedStats, int $minRank, int $maxRank): array {
		$offset = $minRank - 1;
		$length = $maxRank - $offset;
		return array_slice($rankedStats, $offset, $length, preserve_keys: true);
	}

	/**
	 * Get stats from the player table grouped by race and sorted by $stat (high to low).
	 *
	 * @param 'experience'|'kills'|'deaths' $stat
	 * @return array<int, \Smr\DatabaseRecord>
	 */
	public static function raceStats(string $stat, int $gameID): array {
		$db = Database::getInstance();
		$raceStats = [];
		$dbResult = $db->read('SELECT race_id, COALESCE(SUM(' . $stat . '), 0) as amount, count(*) as num_players FROM player WHERE game_id = :game_id GROUP BY race_id ORDER BY amount DESC', [
			'game_id' => $db->escapeNumber($gameID),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$raceStats[$dbRecord->getInt('race_id')] = $dbRecord;
		}
		return $raceStats;
	}

	/**
	 * Get stats from the player table sorted by $stat (high to low).
	 *
	 * @param 'experience'|'kills'|'deaths'|'assists' $stat
	 * @return array<int, \Smr\DatabaseRecord>
	 */
	public static function playerStats(string $stat, int $gameID, ?int $limit = null): array {
		$db = Database::getInstance();
		$playerStats = [];
		$query = 'SELECT player.*, ' . $stat . ' AS amount FROM player WHERE game_id = :game_id ORDER BY amount DESC, player_name';
		if ($limit !== null) {
			$query .= ' LIMIT ' . $limit;
		}
		$dbResult = $db->read($query, [
			'game_id' => $db->escapeNumber($gameID),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$playerStats[$dbRecord->getInt('player_id')] = $dbRecord;
		}
		return $playerStats;
	}

	/**
	 * Gets player stats from the hof table sorted by $category (high to low).
	 *
	 * @param array<string> $category
	 * @return array<int, \Smr\DatabaseRecord>
	 */
	public static function playerStatsFromHOF(array $category, int $gameID): array {
		$db = Database::getInstance();
		$playerStats = [];
		$dbResult = $db->read('SELECT p.*, COALESCE(ph.amount,0) amount FROM player p LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = :hof_type WHERE p.game_id = :game_id ORDER BY amount DESC, player_name', [
			'hof_type' => $db->escapeString(implode(':', $category)),
			'game_id' => $db->escapeNumber($gameID),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$playerStats[$dbRecord->getInt('player_id')] = $dbRecord;
		}
		return $playerStats;
	}

	/**
	 * Gets alliance stats from the hof table sorted by $category (high to low).
	 *
	 * @param array<string> $category
	 * @return array<int, \Smr\DatabaseRecord>
	 */
	public static function allianceStatsFromHOF(array $category, int $gameID): array {
		$db = Database::getInstance();
		$allianceStats = [];
		$dbResult = $db->read('SELECT alliance.*, COALESCE(SUM(amount), 0) amount
			FROM alliance
			LEFT JOIN player p USING (game_id, alliance_id)
			LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = :hof_type
			WHERE p.game_id = :game_id
			GROUP BY alliance_id
			ORDER BY amount DESC, alliance_name', [
			'hof_type' => $db->escapeString(implode(':', $category)),
			'game_id' => $db->escapeNumber($gameID),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$allianceStats[$dbRecord->getInt('alliance_id')] = $dbRecord;
		}
		return $allianceStats;
	}

	/**
	 * Get stats from the alliance table sorted by $stat (high to low).
	 *
	 * @return array<int, \Smr\DatabaseRecord>
	 */
	public static function allianceStats(string $stat, int $gameID, ?int $limit = null): array {
		$db = Database::getInstance();
		$allianceStats = [];
		if ($stat === 'experience') {
			$query = 'SELECT alliance.*, COALESCE(SUM(experience), 0) amount
				FROM alliance
				LEFT JOIN player USING (game_id, alliance_id)
				WHERE game_id = ' . $db->escapeNumber($gameID) . '
				GROUP BY alliance_id
				ORDER BY amount DESC, alliance_name ASC';
		} else {
			$query = 'SELECT alliance.*, alliance_' . $stat . ' AS amount
				FROM alliance WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY amount DESC, alliance_name';
		}
		if ($limit !== null) {
			$query .= ' LIMIT ' . $limit;
		}
		$dbResult = $db->read($query);
		foreach ($dbResult->records() as $dbRecord) {
			$allianceStats[$dbRecord->getInt('alliance_id')] = $dbRecord;
		}
		return $allianceStats;
	}

	/**
	 * Given a $rankedStats array returned by one of the stats functions,
	 * find the rank associated with a specific ID.
	 *
	 * @param array<int, \Smr\DatabaseRecord> $rankedStats
	 */
	public static function ourRank(array $rankedStats, int $ourID): int {
		return array_search($ourID, array_keys($rankedStats)) + 1;
	}

	/**
	 * @return array{int, int}
	 */
	public static function calculateMinMaxRanks(int $ourRank, int $totalRanks): array {
		$session = Session::getInstance();
		$minRank = $session->getRequestVarInt('min_rank', $ourRank - 5);
		$maxRank = $session->getRequestVarInt('max_rank', $ourRank + 5);

		// Swap min and max if user input them in the wrong order
		if ($minRank > $maxRank) {
			[$minRank, $maxRank] = [$maxRank, $minRank];
		}

		if ($minRank <= 0 || $minRank > $totalRanks) {
			$minRank = 1;
		}

		$maxRank = min($maxRank, $totalRanks);

		$template = Template::getInstance();
		$template->assign('MinRank', $minRank);
		$template->assign('MaxRank', $maxRank);
		$template->assign('TotalRanks', $totalRanks);

		return [$minRank, $maxRank];
	}

}
