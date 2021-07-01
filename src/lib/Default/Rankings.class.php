<?php declare(strict_types=1);

class Rankings {

	public static function collectRaceRankings(Smr\DatabaseResult $dbResult, AbstractSmrPlayer $player) : array {
		$rankings = [];
		foreach ($dbResult->records() as $index => $dbRecord) {
			$race_id = $dbRecord->getInt('race_id');
			if ($player->getRaceID() == $race_id) {
				$style = ' class="bold"';
			} else {
				$style = '';
			}

			$rankings[$index + 1] = [
				'style' => $style,
				'race_id' => $dbRecord->getInt('race_id'),
				'amount' => $dbRecord->getInt('amount'),
				'amount_avg' => IRound($dbRecord->getInt('amount') / $dbRecord->getInt('num_players')),
				'num_players' => $dbRecord->getInt('num_players'),
			];
		}
		return $rankings;
	}

	public static function collectAllianceRankings(Smr\DatabaseResult $dbResult, AbstractSmrPlayer $player, int $startRank) : array {
		$rankings = array();
		foreach ($dbResult->records() as $index => $dbRecord) {
			$currentAlliance = SmrAlliance::getAlliance($dbRecord->getInt('alliance_id'), $player->getGameID());

			$class = '';
			if ($player->getAllianceID() == $currentAlliance->getAllianceID()) {
				$class = ' class="bold"';
			} elseif ($currentAlliance->hasDisbanded()) {
				$class = ' class="red"';
			}

			$rankings[$startRank + $index] = array(
				'Alliance' => $currentAlliance,
				'Class' => $class,
				'Value' => $dbRecord->getInt('amount')
			);
		}
		return $rankings;
	}

	public static function collectRankings(Smr\DatabaseResult $dbResult, AbstractSmrPlayer $player, int $startRank) : array {
		$rankings = array();
		foreach ($dbResult->records() as $index => $dbRecord) {
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

			$rankings[$startRank + $index] = array(
				'Player' => $currentPlayer,
				'Class' => $class,
				'Value' => $dbRecord->getInt('amount')
			);
		}
		return $rankings;
	}

	/**
	 * Get a subset of rankings from the player table sorted by $stat.
	 */
	public static function playerRanks(string $stat, int $minRank = 1, int $maxRank = 10) : array {
		$session = Smr\Session::getInstance();
		$db = Smr\Database::getInstance();

		$offset = $minRank - 1;
		$limit = $maxRank - $offset;
		$dbResult = $db->read('SELECT *, ' . $stat . ' AS amount FROM player WHERE game_id = ' . $db->escapeNumber($session->getGameID()) . ' ORDER BY amount DESC, player_name LIMIT ' . $offset . ', ' . $limit);
		return self::collectRankings($dbResult, $session->getPlayer(), $minRank);
	}

	/**
	 * Get a subset of rankings from the alliance table sorted by $stat.
	 */
	public static function allianceRanks(string $stat, int $minRank = 1, int $maxRank = 10) : array {
		$session = Smr\Session::getInstance();
		$db = Smr\Database::getInstance();

		$offset = $minRank - 1;
		$limit = $maxRank - $offset;
		$dbResult = $db->read('SELECT alliance_id, alliance_' . $stat . ' AS amount FROM alliance WHERE game_id = ' . $db->escapeNumber($session->getGameID()) . ' ORDER BY amount DESC, alliance_name LIMIT ' . $offset . ', ' . $limit);
		return self::collectAllianceRankings($dbResult, $session->getPlayer(), $minRank);
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
