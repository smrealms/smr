<?php declare(strict_types=1);

use Smr\TransactionType;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$player = $session->getPlayer();

		$template->assign('PageTopic', 'Negotiate Price');

		// creates needed objects
		$port = $player->getSectorPort();
		// get values from request
		$good_id = $var['good_id'];
		$portGood = Globals::getGood($good_id);
		$transaction = $port->getGoodTransaction($good_id);

		// Has the player failed a bargain?
		if ($var['bargain_price'] > 0) {
			$bargain_price = $var['bargain_price'];
			$template->assign('OfferToo', match ($transaction) {
				TransactionType::Sell => 'high',
				TransactionType::Buy => 'low',
			});
		} else {
			$bargain_price = $var['offered_price'];
		}

		$template->assign('PortAction', strtolower($transaction->opposite()->value));

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

		$container = Page::create('shop_goods.php');
		$template->assign('ShopHREF', $container->href());

		$container = Page::create('current_sector.php');
		$template->assign('LeaveHREF', $container->href());
