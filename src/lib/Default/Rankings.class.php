<?php declare(strict_types=1);

class Rankings {
	private function __construct() {}

	public static function collectRaceRankings(MySqlDatabase $db, AbstractSmrPlayer $player) {
		$rankings = [];
		$rank = 0;
		while ($db->nextRecord()) {
			// increase rank counter
			$rank++;

			$race_id = $db->getInt('race_id');
			if ($player->getRaceID() == $race_id) {
				$style = ' class="bold"';
			} else {
				$style = '';
			}

			$rankings[$rank] = [
				'style' => $style,
				'race_id' => $db->getInt('race_id'),
				'amount' => $db->getInt('amount'),
				'amount_avg' => IRound($db->getInt('amount') / $db->getInt('num_players')),
				'num_players' => $db->getInt('num_players'),
			];
		}
		return $rankings;
	}

	public static function collectAllianceRankings(MySqlDatabase $db, AbstractSmrPlayer $player, $rank) {
		$rankings = array();
		while ($db->nextRecord()) {
			// increase rank counter
			$rank++;
			$currentAlliance = SmrAlliance::getAlliance($db->getInt('alliance_id'), $player->getGameID());

			$class = '';
			if ($player->getAllianceID() == $currentAlliance->getAllianceID()) {
				$class = ' class="bold"';
			} elseif ($currentAlliance->hasDisbanded()) {
				$class = ' class="red"';
			}

			$rankings[$rank] = array(
				'Rank' => $rank,
				'Alliance' => $currentAlliance,
				'Class' => $class,
				'Value' => $db->getInt('amount')
			);
		}
		return $rankings;
	}

	public static function collectRankings(MySqlDatabase $db, AbstractSmrPlayer $player, $rank) {
		$rankings = array();
		while ($db->nextRecord()) {
			// increase rank counter
			$rank++;
			$currentPlayer = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID(), false, $db);

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

			$rankings[$rank] = array(
				'Rank' => $rank,
				'Player' => $currentPlayer,
				'Class' => $class,
				'Value' => $db->getInt('amount')
			);
		}
		return $rankings;
	}

	/**
	 * Get a subset of rankings from the player table sorted by $stat.
	 */
	public static function playerRanks(string $stat, int $minRank = 1, int $maxRank = 10) : array {
		$session = Smr\Session::getInstance();
		$db = MySqlDatabase::getInstance();

		$offset = $minRank - 1;
		$limit = $maxRank - $offset;
		$db->query('SELECT *, ' . $stat . ' AS amount FROM player WHERE game_id = ' . $db->escapeNumber($session->getGameID()) . ' ORDER BY amount DESC, player_name LIMIT ' . $offset . ', ' . $limit);
		return self::collectRankings($db, $session->getPlayer(), $offset);
	}

	/**
	 * Get a subset of rankings from the alliance table sorted by $stat.
	 */
	public static function allianceRanks(string $stat, int $minRank = 1, int $maxRank = 10) : array {
		$session = Smr\Session::getInstance();
		$db = MySqlDatabase::getInstance();

		$offset = $minRank - 1;
		$limit = $maxRank - $offset;
		$db->query('SELECT alliance_id, alliance_' . $stat . ' AS amount FROM alliance WHERE game_id = ' . $db->escapeNumber($session->getGameID()) . ' ORDER BY amount DESC, alliance_name LIMIT ' . $offset . ', ' . $limit);
		return self::collectAllianceRankings($db, $session->getPlayer(), $offset);
	}

	public static function calculateMinMaxRanks($ourRank, $totalRanks) {
		global $var, $template;
		$session = Smr\Session::getInstance();
		$minRank = $session->getRequestVarInt('min_rank', $ourRank - 5);
		$maxRank = $session->getRequestVarInt('max_rank', $ourRank + 5);

		if ($minRank <= 0 || $minRank > $totalRanks) {
			$minRank = 1;
		}

		$maxRank = min($maxRank, $totalRanks);

		$template->assign('MinRank', $minRank);
		$template->assign('MaxRank', $maxRank);
		$template->assign('TotalRanks', $totalRanks);

		return [$minRank, $maxRank];
	}
}
