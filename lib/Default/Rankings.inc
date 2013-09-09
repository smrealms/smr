<?php
require_once(get_file_loc('SmrPlayer.class.inc'));
class Rankings {
	private function __construct() {
	}

	public static function &collectAllianceRankings(SmrMySqlDatabase &$db, AbstractSmrPlayer &$player, $rank) {
		$rankings = array();
		while ($db->nextRecord()) {
			// increase rank counter
			$rank++;
			$currentAlliance =& SmrAlliance::getAlliance($db->getInt('alliance_id'), $player->getGameID());

			$class = '';
			if ($player->getAllianceID() == $currentAlliance->getAllianceID()) {
				$class = ' class="bold"';
			}
			else if($currentAlliance->hasDisbanded()) {
				$class = ' class="red"';
			}

			$rankings[$rank] = array(
				'Rank' => $rank,
				'Alliance' => &$currentAlliance,
				'Class' => $class,
				'Value' => $db->getInt('amount')
			);
		}
		return $rankings;
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
		elseif(isset($var['MinRank']) && isset($var['MaxRank'])) {
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

		SmrSession::updateVar('MinRank',$minRank);
		SmrSession::updateVar('MaxRank',$maxRank);
		$template->assign('MinRank', $minRank);
		$template->assign('MaxRank', $maxRank);
		$template->assign('TotalRanks', $totalRanks);
	}

    public static function addRanking(SmrMySqlDatabase &$db, &$account, &$label, &$experience, &$kills, &$operation, &$utility){
        $position = 5;
        $db->query("SELECT max(position) as position FROM ranking");
        if($db->nextRecord()){
            $row = $db->getRow();
            $position = ($row['position'] == null) ? $position:$row['position'];
        }
        $position += 10;
        $query = "INSERT INTO ranking (label, experience, kills, operation, utility, position, created_by, created_at) "
                    ."VALUES (".$db->escapeString($label).", ".$db->escapeNumber($experience)
                    .", ".$db->escapeNumber($kills).", ".$db->escapeNumber($operation)
                    .", ".$db->escapeNumber($utility).", ".$db->escapeNumber($position)
                    .", ".$account->getAccountID().", ".time().")";
        $db->query($query);

    }

    public static function moveRanking(SmrMySqlDatabase &$db, &$rankingId, $direction){

        $db->query("SELECT * FROM ranking WHERE ranking_id=".$db->escapeNumber($rankingId));
        if(!$db->nextRecord()){
            return;
        }
        $ranking = $db->fetch_row();

        switch($direction){
            case "up":
                $db->query("SELECT * FROM ranking where position < ".$ranking['position']." ORDER BY position DESC");
                if(!$db->nextRecord()){
                    return;
                }
                $otherRanking = $db->fetch_row();
                $db->query("UPDATE ranking SET position=".$ranking['position']." WHERE ranking_id=".$otherRanking['ranking_id']);
                $db->query("UPDATE ranking SET position=".$otherRanking['position']." WHERE ranking_id=".$ranking['ranking_id']);
            break;

            case "down":
                $db->query("SELECT * FROM ranking where position > ".$ranking['position']." ORDER BY position ");
                if(!$db->nextRecord()){
                    return;
                }
                $otherRanking = $db->fetch_row();
                $db->query("UPDATE ranking SET position=".$ranking['position']." WHERE ranking_id=".$otherRanking['ranking_id']);
                $db->query("UPDATE ranking SET position=".$otherRanking['position']." WHERE ranking_id=".$ranking['ranking_id']);
            break;

            default:
                create_error("unknown direction, dude ");
                break;
        }

    }

    public static function deleteRanking(SmrMySqlDatabase &$db, &$rankingId){
        $db->query("DELETE FROM ranking WHERE ranking_id=".$db->escapeNumber($rankingId));
    }

    public static function getRankings(SmrMySqlDatabase &$db){
        $rankings = array();
        $query = "SELECT * FROM ranking ORDER BY position";
        $db->query($query);
        while($db->nextRecord()) {
            $a = array();
            $r = $db->getRow();
            $a['rankingId'] = $r['ranking_id'];
            $a['label'] = $r['label'];
            $a['experience'] = $r['experience'];
            $a['kills'] = $r['kills'];
            $a['operation'] = $r['operation'];
            $a['utility'] = $r['utility'];
            $rankings[] = $a;
        }
        return $rankings;
    }

    public static function getAccountRanking(SmrMySqlDatabase &$db, &$account){

        $db->query("SELECT SUM(score) operation FROM player_operation po,  account a "
            ." WHERE po.account_id=a.account_id "
            ." AND a.account_id=".$db->escapeNumber($account->getAccountID()));

        $row = $db->fetch_row();
        return ($row)? $row['operation'] : 0;
    }

    public static function getPlayerOperationScore(SmrMySqlDatabase &$db, &$player){
        $retVal = array();
        $query = "SELECT sum(score) AS score FROM player_operation WHERE account_id=". $player->getAccountID();
        $db->query($query);
        if($db->nextRecord()){
            $retVal['score'] = $db->getRow()['score'];
        }
        $query = "SELECT * FROM ranking";
        $db->query($query);
        while($db->nextRecord()){
            $row = $db->getRow();
            if($row['operation'] < $retVal['score']){
                $retVal['currRanking'] = $row['label'];
            }
            if($row['operation'] > $retVal['score']){
                $retVal['nextRanking'] = $row['label'];
                break;
            }
        }
        return $retVal;
    }

    public static function getGamePlayerOperationRanking(SmrMySqlDatabase &$db, $gameId){
        $arr = array();
        $query = "SELECT account_id,  SUM(score) AS amount FROM player_operation  "
                    ." WHERE game_id=".$db->escapeNumber($gameId)
                    ." GROUP BY account_id "
                    ." ORDER BY SUM(score) DESC";
        $db->query($query);

        while($db->nextRecord()){
            $arr[] = $db->getRow();
        }
        return $arr;
    }

    public static function &collectRankingsViaArray(&$arr, AbstractSmrPlayer &$player, $offset, $quantity) {
        $rankings = array();

        foreach($arr as $rank => $obj) {
            if($rank+1 >= $offset && $rank+1 < $offset+$quantity){
                $currentPlayer =& SmrPlayer::getPlayer($obj['account_id'], $player->getGameID());

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
                    'Rank' => $rank+1,
                    'Player' => &$currentPlayer,
                    'Class' => $class,
                    'Value' => $obj['amount']
                );
            }

        }
        return $rankings;
    }

    public static function getGameAllianceOperationRanking(SmrMySqlDatabase &$db, &$gameId){
        $arr = array();
        $query = " SELECT  a.alliance_name, p.alliance_id, SUM(po.score) as amount"
                ." FROM player_operation po, alliance a,  player p "
                ." WHERE po.game_id= ".$db->escapeNumber($gameId)
				." AND a.game_id=".$db->escapeNumber($gameId)
                ." AND p.alliance_id > 0 "
                ." AND po.account_id = p.account_id "
			    ." AND po.game_id = p.game_id "
			    ." AND p.alliance_id = a.alliance_id "
                ." GROUP BY p.alliance_id "
                ." ORDER BY SUM(score) DESC";

        $db->query($query);

        while($db->nextRecord()){
            $arr[] = $db->getRow();
        }
        return $arr;
    }

    public static function &collectAllianceRankingsViaArray(&$arr, AbstractSmrPlayer &$player, $offset, $quantity) {
        $rankings = array();
        foreach($arr as $rank => $obj) {
            if($rank+1 >= $offset && $rank+1 < $offset+$quantity){
                $currentAlliance =& SmrAlliance::getAlliance($obj['alliance_id'], $player->getGameID());

                $class = '';
                if ($player->getAllianceID() == $currentAlliance->getAllianceID()) {
                    $class = ' class="bold"';
                }
                else if($currentAlliance->hasDisbanded()) {
                    $class = ' class="red"';
                }

                $rankings[$rank] = array(
                    'Rank' => $rank,
                    'Alliance' => &$currentAlliance,
                    'Class' => $class,
                    'Value' => $obj['amount']
                );
            }
        }
        return $rankings;
    }
}
?>