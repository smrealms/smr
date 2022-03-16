<?php declare(strict_types=1);

// Returns an array with all relevant information about draft teams,
// including their current size and if the leader can pick teammates.
function get_draft_teams(int $gameId): array {
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT account_id FROM draft_leaders WHERE game_id=' . $db->escapeNumber($gameId));

	// Get team leader, alliance, and alliance size
	$teams = [];
	foreach ($dbResult->records() as $dbRecord) {
		$leader = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $gameId);
		if (!$leader->hasAlliance() || $leader->getAlliance()->isNHA()) {
			// Special case for leaders who haven't made their own alliance yet,
			// or are still in the Newbie Help Alliance.
			$teams[$leader->getAccountID()] = [
				'Leader' => $leader,
				'Size' => 0,
			];
		} else {
			$alliance = $leader->getAlliance();
			$teams[$leader->getAccountID()] = [
				'Leader' => $leader,
				'Alliance' => $alliance,
				'Size' => $alliance->getNumMembers(),
			];
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
