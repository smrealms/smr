<?php
require_once(get_file_loc('SmrPlayer.class.inc'));
class Rankings {
	private function __construct() {
	}

	public static function &collectRankings(SmrMySqlDatabase &$db, AbstractSmrPlayer &$player, $rank) {
		$rankings = array();
		while ($db->nextRecord()) {
			// increase rank counter
			$rank++;
			$currentPlayer =& SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID());

			$class='';
			if ($player->equals($currentPlayer)) {
				$class .= 'bold';
			}
			if($currentPlayer->getAccount()->isNewbie()) {
				$class.= ' newbie';
			}
			if($class!='') {
				$class = ' class="'.trim($class).'"';
			}

			$rankings[$rank] = array(
				'Rank' => $rank,
				'Player' => &$currentPlayer,
				'Class' => $class,
				'Value' => number_format($db->getInt('amount'))
			);
		}
		return $rankings;
	}

	public static function calculateMinMaxRanks($ourRank, $totalPlayers) {
		global $var, $template;
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'Show' && is_numeric($_REQUEST['min_rank']) && is_numeric($_REQUEST['max_rank'])) {
			$minRank = min($_REQUEST['min_rank'], $_REQUEST['max_rank']);
			$maxRank = max($_REQUEST['min_rank'], $_REQUEST['max_rank']);
		}
		elseif(isset($var['MinRank']) && isset($var['MaxRank'])) {
			$minRank = $var['MinRank'];
			$maxRank = $var['MaxRank'];
		}
		else {
			$minRank = $ourRank - 5;
			$maxRank = $ourRank + 5;
		}

		if ($minRank <= 0 || $minRank > $totalPlayers) {
			$minRank = 1;
			$maxRank = 10;
		}

		$maxRank = min($maxRank, $totalPlayers);

		SmrSession::updateVar('MinRank',$minRank);
		SmrSession::updateVar('MaxRank',$maxRank);
		$template->assign('MaxRank', $minRank);
		$template->assign('MinRank', $maxRank);
	}
}

?>