<?php

if (isset($var['process'])) {
	if ($account->getTotalSmrCredits() < CREDITS_PER_TICKER) {
		create_error('You don\'t have enough SMR Credits.  Donate money to SMR to gain SMR Credits!');
	}
	if(isset($_REQUEST['type'])) {
		SmrSession::updateVar('type',$_REQUEST['type']);
	}
	$type = $var['type'];
	if(empty($type)) {
		create_error('You have to choose the type of ticker to buy.');
	}
	switch($type) {
		case 'NEWS':
		case 'SCOUT':
		case 'BLOCK':
		break;
		default:
			create_error('The ticker you chose does not exist.');
	}
	$expires = TIME;
	$ticker = $player->getTicker($type);
	if($ticker !== false) {
		$expires = $ticker['Expires'];
	}
	$expires += 5*86400;
	$db->query('REPLACE INTO player_has_ticker (game_id, account_id, type, expires) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($type) . ', ' . $db->escapeNumber($expires) . ')');
	//take credits
	$account->decreaseTotalSmrCredits(CREDITS_PER_TICKER);
	//offer another drink and such
	$container=create_container('skeleton.php','bar_main.php');
	transfer('LocationID');
	$container['message'] = '<div class="center">Your system has been added.  Enjoy!</div><br />';
	forward($container);
}
else {
	// This is a display page!
	$template->assign('PageTopic', 'Buy System');
	Menu::bar();

	//they can buy the ticker...first we need to find out what they want
	$tickers = [];
	foreach ($player->getTickers() as $ticker) {
		$type = $ticker['Type'];
		if ($ticker['Type'] == 'NEWS') {
			$type = 'News Ticker';
		}
		if ($ticker['Type'] == 'SCOUT') {
			$type = 'Scout Message Ticker';
		}
		if ($ticker['Type'] == 'BLOCK') {
			$type = 'Scout Message Blocker';
		}
		$tickers[$type] = $ticker['Expires'] - TIME;
	}
	$template->assign('Tickers', $tickers);

	$container = create_container('skeleton.php', 'bar_ticker_buy.php');
	transfer('LocationID');
	$container['process'] = 'yes';
	$template->assign('BuyHREF', SmrSession::getNewHREF($container));
}
