<?php

// Returns the number of members in the smallest alliance (NHA excluded).
// Used to determine whose turn it is to pick in the Draft.
function min_alliance_members($gameId) {
	$db = new SmrMySqlDatabase();
	$db->query('
	SELECT MIN(alliance_member) min_members
	FROM
	(
		SELECT COUNT(*) alliance_member
		FROM player
		WHERE game_id='.$db->escapeNumber($gameId).' AND alliance_id!='.$db->escapeNumber(NHA_ID).' AND alliance_id!=0
		GROUP BY alliance_id
	) t');
	$db->nextRecord();

	return $db->getInt('min_members');
}

?>
