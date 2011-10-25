<?php

foreach ($_POST['role'] as $account_id => $role_id)
{
	$db->query('REPLACE INTO player_has_alliance_role
				(account_id, game_id, role_id, alliance_id)
				VALUES ('.$account_id.', '.$player->getGameID().', '.$role_id.','.$var['alliance_id'].')');
}

$container=array();
$container['url'] = 'skeleton.php';
$container['body'] = 'alliance_roster.php';
$container['action'] = 'Show Alliance Roles';
forward($container);

?>