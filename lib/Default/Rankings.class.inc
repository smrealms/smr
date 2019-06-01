<?php

class Rankings {
	private function __construct() {}

	public static function &collectAllianceRankings(SmrMySqlDatabase &$db, AbstractSmrPlayer &$player, $rank) {
		$rankings = array();
		while ($db->nextRecord()) {
			// increase rank counter
			$rank++;
			$currentAlliance = SmrAlliance::getAlliance($db->getInt('alliance_id'), $player->getGameID());

			$class = '';
			if ($player->getAllianceID() == $currentAlliance->getAllianceID()) {
				$class = ' class="bold"';
			}
			else if ($currentAlliance->hasDisbanded()) {
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

	public static function &collectRankings(SmrMySqlDatabase &$db, AbstractSmrPlayer $player, $rank) {
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
		}
		elseif (isset($var['MinRank']) && isset($var['MaxRank'])) {
			$minRank = $var['MinRank'];
			$maxRank = $var['MaxRank'];
		}
		else {
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
