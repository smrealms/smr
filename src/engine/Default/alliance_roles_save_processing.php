<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

foreach (Smr\Request::getIntArray('role', []) as $accountID => $roleID) {
	$db = Smr\Database::getInstance();
	$db->replace('player_has_alliance_role', [
		'account_id' => $db->escapeNumber($accountID),
		'game_id' => $db->escapeNumber($player->getGameID()),
		'role_id' => $db->escapeNumber($roleID),
		'alliance_id' => $db->escapeNumber($var['alliance_id']),
	]);
}

$container = Page::create('alliance_roster.php');
$container['action'] = 'Show Alliance Roles';
$container->go();
