<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Negotiate Price');
require_once(LIB . 'Default/shop_goods.inc.php');
// creates needed objects
$port = $player->getSectorPort();
// get values from request
$good_id = $var['good_id'];
$portGood = Globals::getGood($good_id);
$transaction = $port->getGoodTransaction($good_id);

// Has the player failed a bargain?
if ($var['bargain_price'] > 0) {
	$bargain_price = $var['bargain_price'];

	if ($transaction === TRADER_SELLS) {
		$template->assign('OfferToo', 'high');
	} elseif ($transaction === TRADER_BUYS) {
		$template->assign('OfferToo', 'low');
	}
} else {
	$bargain_price = $var['offered_price'];
}

if ($transaction === TRADER_SELLS) {
	$template->assign('PortAction', 'buy');
} elseif ($transaction === TRADER_BUYS) {
	$template->assign('PortAction', 'offer you');
}

$container = Page::create('shop_goods_processing.php');
$container->addVar('amount');
$container->addVar('good_id');
$container->addVar('offered_price');
$container->addVar('ideal_price');
$container->addVar('number_of_bargains');
$container->addVar('overall_number_of_bargains');
$template->assign('BargainHREF', $container->href());

$template->assign('BargainPrice', $bargain_price);
$template->assign('OfferedPrice', $var['offered_price']);
$template->assign('Transaction', $transaction);
$template->assign('Good', $portGood);
$template->assign('Amount', $var['amount']);
$template->assign('Port', $port);

$container = Page::create('skeleton.php', 'shop_goods.php');
$template->assign('ShopHREF', $container->href());

$container = Page::create('skeleton.php', 'current_sector.php');
$template->assign('LeaveHREF', $container->href());
