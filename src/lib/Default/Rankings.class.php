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

	public static function calculateMinMaxRanks($ourRank, $totalRanks) {
		global $var, $template;
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'Show' && is_numeric($_REQUEST['min_rank']) && is_numeric($_REQUEST['max_rank'])) {
			$minRank = min($_REQUEST['min_rank'], $_REQUEST['max_rank']);
			$maxRank = max($_REQUEST['min_rank'], $_REQUEST['max_rank']);
		} elseif (isset($var['MinRank']) && isset($var['MaxRank'])) {
			$minRank = $var['MinRank'];
			$maxRank = $var['MaxRank'];
		} else {
			$minRank = $ourRank - 5;
			$maxRank = $ourRank + 5;
		}

		if ($minRank <= 0 || $minRank > $totalRanks) {
			$minRank = 1;
			$maxRank = 10;
		}

		$maxRank = min($maxRank, $totalRanks);

		SmrSession::updateVar('MinRank', $minRank);
		SmrSession::updateVar('MaxRank', $maxRank);
		$template->assign('MinRank', $minRank);
		$template->assign('MaxRank', $maxRank);
		$template->assign('TotalRanks', $totalRanks);
	}
}
