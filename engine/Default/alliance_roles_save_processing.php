<?php
if(count($_POST['role']) > 0)
{
	foreach ($_POST['role'] as $accountID => $roleID)
	{
		$db->query('REPLACE INTO player_has_alliance_role
					(account_id, game_id, role_id, alliance_id)
					VALUES ('.$db->escapeNumber($accountID).', '.$player->getGameID().', '.$db->escapeNumber($roleID).','.$var['alliance_id'].')');
	}
}
	
$container=create_container('skeleton.php','alliance_roster.php');
$container['action'] = 'Show Alliance Roles';
forward($container);

?>