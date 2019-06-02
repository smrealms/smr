<?php
if (count($_REQUEST['role']) > 0) {
	foreach ($_REQUEST['role'] as $accountID => $roleID) {
		$db->query('REPLACE INTO player_has_alliance_role
					(account_id, game_id, role_id, alliance_id)
					VALUES (' . $db->escapeNumber($accountID) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($roleID) . ',' . $db->escapeNumber($var['alliance_id']) . ')');
	}
}

$container = create_container('skeleton.php', 'alliance_roster.php');
$container['action'] = 'Show Alliance Roles';
forward($container);
