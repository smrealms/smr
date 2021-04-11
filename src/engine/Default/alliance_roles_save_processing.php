<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

foreach (Request::getIntArray('role', []) as $accountID => $roleID) {
	$db = Smr\Database::getInstance();
	$db->query('REPLACE INTO player_has_alliance_role
					(account_id, game_id, role_id, alliance_id)
					VALUES (' . $db->escapeNumber($accountID) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($roleID) . ',' . $db->escapeNumber($var['alliance_id']) . ')');
}

$container = Page::create('skeleton.php', 'alliance_roster.php');
$container['action'] = 'Show Alliance Roles';
$container->go();
