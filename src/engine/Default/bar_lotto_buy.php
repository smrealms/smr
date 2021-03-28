<?php declare(strict_types=1);

$template->assign('PageTopic', 'Galactic Lotto');
Menu::bar();

require_once(get_file_loc('bar.inc.php'));
checkForLottoWinner($player->getGameID());
$lottoInfo = getLottoInfo($player->getGameID());
$template->assign('LottoInfo', $lottoInfo);

$container = Page::create('bar_lotto_buy_processing.php');
$container->addVar('LocationID');
$template->assign('BuyTicketHREF', $container->href());
