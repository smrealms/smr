<?php declare(strict_types=1);

use Smr\Lotto;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		$template->assign('PageTopic', 'Galactic Lotto');
		Menu::bar();

		Lotto::checkForLottoWinner($player->getGameID());
		$lottoInfo = Lotto::getLottoInfo($player->getGameID());
		$template->assign('LottoInfo', $lottoInfo);

		$container = Page::create('bar_lotto_buy_processing.php');
		$container->addVar('LocationID');
		$template->assign('BuyTicketHREF', $container->href());
