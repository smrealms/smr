<?php declare(strict_types=1);

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
	$tickers[$type] = $ticker['Expires'] - SmrSession::getTime();
}
$template->assign('Tickers', $tickers);

$container = Page::create('skeleton.php', 'bar_ticker_buy_processing.php');
$container->addVar('LocationID');
$template->assign('BuyHREF', $container->href());
