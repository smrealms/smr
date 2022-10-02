<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

//get bar name
$location = SmrLocation::getLocation($player->getGameID(), $var['LocationID']);
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
$db = Database::getInstance();
$dbResult = $db->read('SELECT prize FROM player_has_ticket WHERE ' . $player->getSQL() . ' AND time = 0');
if ($dbResult->hasRecord()) {
	$winningTicket = $dbResult->record()->getInt('prize');

	$container = Page::create('bar_lotto_claim.php');
	$container->addVar('LocationID');
	$template->assign('LottoClaimHREF', $container->href());
}
$template->assign('WinningTicket', $winningTicket);

//get rid of drinks older than 30 mins
$db->write('DELETE FROM player_has_drinks WHERE time < ' . $db->escapeNumber(Epoch::time() - 1800));

$container = Page::create('bar_talk_bartender.php');
$container->addVar('LocationID');
$template->assign('GossipHREF', $container->href());

$container = Page::create('bar_buy_drink_processing.php');
$container->addVar('LocationID');
$container['action'] = 'drink';
$template->assign('BuyDrinkHREF', $container->href());
$container['action'] = 'water';
$template->assign('BuyWaterHREF', $container->href());

$container = Page::create('bar_ticker_buy.php');
$container->addVar('LocationID');
$template->assign('BuySystemHREF', $container->href());

$container = Page::create('bar_galmap_buy.php');
$container->addVar('LocationID');
$template->assign('BuyGalMapHREF', $container->href());
