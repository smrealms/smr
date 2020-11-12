<?php declare(strict_types=1);

// Returns an array with all relevant information about draft teams,
// including their current size and if the leader can pick teammates.
function get_draft_teams($gameId) {
	$db = new SmrMySqlDatabase();
	$db->query('SELECT player_id FROM draft_leaders WHERE game_id=' . $db->escapeNumber($gameId));

	// Get team leader, alliance, and alliance size
	$teams = array();
	while ($db->nextRecord()) {
		$leader = SmrPlayer::getPlayer($db->getInt('player_id'), $gameId);
		$alliance = $leader->getAlliance();
		if (!$leader->hasAlliance() || $alliance->getAllianceID() == NHA_ID) {
			// Special case for leaders who haven't made their own alliance yet,
			// or are still in the Newbie Help Alliance.
			$teams[$leader->getPlayerID()] = array('Leader'   => $leader,
			                                       'Size'     => 0);
		} else {
			$teams[$leader->getPlayerID()] = array('Leader'   => $leader,
			                                       'Alliance' => $alliance,
			                                       'Size'     => $alliance->getNumMembers());
		}
	}

	// Determine the smallest team alliance size.
	$minSize = min(array_map(function($i) { return $i['Size']; }, $teams));

	// Teams can pick only if their size is not larger than the smallest team.
	foreach ($teams as &$team) {
		if ($minSize == 0) {
			// This means that at least one leader hasn't made an alliance,
			// no one should be picking yet.
			$team['CanPick'] = false;
		} else {
			$team['CanPick'] = $team['Size'] <= $minSize;
		}
	}

  return $teams;
}
