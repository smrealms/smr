<?php
$template->assign('PageTopic','Negotiate Price');
require_once(LIB . 'Default/shop_goods.inc');
// creates needed objects
$port = $player->getSectorPort();
// get values from request
$good_id = $var['good_id'];
$portGood = $port->getGood($good_id);
$transaction = $port->getGoodTransaction($good_id);

// Has the player failed a bargain?
if ($var['bargain_price'] > 0) {
	$bargain_price = $var['bargain_price'];

	if ($transaction == 'Sell') {
		$template->assign('OfferToo', 'high');
	} elseif ($transaction == 'Buy') {
		$template->assign('OfferToo', 'low');
	}
} else {
	$bargain_price = $var['offered_price'];
}

if ($transaction == 'Sell') {
	$template->assign('PortAction', 'buy');
} elseif ($transaction == 'Buy') {
	$template->assign('PortAction', 'offer you');
}

$container = create_container('shop_goods_processing.php');
transfer('amount');
transfer('good_id');
transfer('offered_price');
transfer('ideal_price');
transfer('number_of_bargains');
transfer('overall_number_of_bargains');
$template->assign('BargainHREF', SmrSession::getNewHREF($container));

$template->assign('BargainPrice', $bargain_price);
$template->assign('OfferedPrice', $var['offered_price']);
$template->assign('Transaction', $transaction);
$template->assign('Good', $portGood);
$template->assign('Amount', $var['amount']);
$template->assign('Port', $port);

$container = create_container('skeleton.php', 'shop_goods.php');
$template->assign('ShopHREF', SmrSession::getNewHREF($container));

$container = create_container('skeleton.php', 'current_sector.php');
$template->assign('LeaveHREF', SmrSession::getNewHREF($container));
