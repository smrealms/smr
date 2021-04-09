<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Place Bounty');

Menu::headquarters();

$container = Page::create('bounty_place_processing.php');
$container->addVar('LocationID');
$template->assign('SubmitHREF', $container->href());

$bountyPlayers = [];
$db->query('SELECT player_id, player_name FROM player JOIN account USING(account_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id != ' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY player_name');
while ($db->nextRecord()) {
	$bountyPlayers[$db->getInt('player_id')] = htmlentities($db->getField('player_name'));
}
$template->assign('BountyPlayers', $bountyPlayers);
