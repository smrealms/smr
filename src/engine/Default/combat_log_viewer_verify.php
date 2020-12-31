<?php declare(strict_types=1);
// Verify that the player is permitted to view the requested combat log
// Qualifications:
//  * Log must be from the current game
//  * Attacker or defender is the player OR in the player's alliance

$query = 'SELECT log_id FROM combat_logs WHERE log_id=' . $db->escapeNumber($var['log_id']) . ' AND game_id=' . $db->escapeNumber($player->getGameID()) . ' AND ';
if ($player->hasAlliance()) {
	$query .= '(attacker_alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' OR defender_alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ')';
} else {
	$query .= '(attacker_id=' . $db->escapeNumber($player->getAccountID()) . ' OR defender_id=' . $db->escapeNumber($player->getAccountID()) . ')';
}
$db->query($query . ' LIMIT 1');

// Error if qualifications are not met
if (!$db->nextRecord()) {
	create_error('You do not have permission to view this combat log!');
}

// Player has permission, so go to the display page!
$container = create_container('skeleton.php', 'combat_log_viewer.php');
$container['log_ids'] = array($var['log_id']);
$container['current_log'] = 0;
forward($container);
