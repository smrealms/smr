<?php

//first check if there is a bar here
if (!$sector->hasBar()) {
	create_error('So two guys walk into this bar...');
}

//get bar name
$location = SmrLocation::getLocation($var['LocationID']);
$template->assign('PageTopic', 'Welcome to ' . $location->getName());
Menu::bar();

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
} else {
	$template->assign('Message', '<i>You enter and take a seat at the bar.
	                              The bartender looks like the helpful type.</i>');
}

$winningTicket = false;
//check for winner
$db->query('SELECT prize FROM player_has_ticket WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND time = 0 LIMIT 1');
if ($db->nextRecord()) {
	$winningTicket = $db->getInt('prize');

	$container = create_container('bar_lotto_claim.php');
	transfer('LocationID');
	$template->assign('LottoClaimHREF', SmrSession::getNewHREF($container));
}
$template->assign('WinningTicket',$winningTicket);

//get rid of drinks older than 30 mins
$db->query('DELETE FROM player_has_drinks WHERE time < ' . $db->escapeNumber(TIME - 1800));

$container = create_container('skeleton.php', 'bar_talk_bartender.php');
transfer('LocationID');
$template->assign('GossipHREF', SmrSession::getNewHREF($container));

$container = create_container('bar_buy_drink_processing.php');
transfer('LocationID');
$container['action'] = 'drink';
$template->assign('BuyDrinkHREF', SmrSession::getNewHREF($container));
$container['action'] = 'water';
$template->assign('BuyWaterHREF', SmrSession::getNewHREF($container));

$container = create_container('skeleton.php', 'bar_ticker_buy.php');
transfer('LocationID');
$template->assign('BuySystemHREF', SmrSession::getNewHREF($container));

$container = create_container('skeleton.php', 'bar_galmap_buy.php');
transfer('LocationID');
$template->assign('BuyGalMapHREF', SmrSession::getNewHREF($container));
