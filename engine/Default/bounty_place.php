<?php declare(strict_types=1);

$template->assign('PageTopic', 'Place Bounty');

Menu::headquarters();

$container = create_container('bounty_place_processing.php');
transfer('LocationID');
$template->assign('SubmitHREF', SmrSession::getNewHREF($container));

$bountyPlayers = [];
$db->query('SELECT player_id, player_name FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND player_id != ' . $db->escapeNumber($player->getPlayerID()) . ' ORDER BY player_name');
while ($db->nextRecord()) {
	$bountyPlayers[$db->getInt('player_id')] = htmlentities($db->getField('player_name'));
}
$template->assign('BountyPlayers', $bountyPlayers);
