<?php declare(strict_types=1);

foreach (Request::getIntArray('role', []) as $playerID => $roleID) {
	$db->query('REPLACE INTO player_has_alliance_role
					(player_id, game_id, role_id, alliance_id)
					VALUES (' . $db->escapeNumber($playerID) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($roleID) . ',' . $db->escapeNumber($var['alliance_id']) . ')');
}

$container = create_container('skeleton.php', 'alliance_roster.php');
$container['action'] = 'Show Alliance Roles';
forward($container);
