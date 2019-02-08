<?php

$template->assign('PageTopic', 'Galactic Lotto');
Menu::bar();

require_once(get_file_loc('bar.functions.inc'));
checkForLottoWinner($player->getGameID());
$lottoInfo = getLottoInfo($player->getGameID());
$template->assign('LottoInfo', $lottoInfo);

$container = create_container('bar_lotto_buy_processing.php');
transfer('LocationID');
$template->assign('BuyTicketHREF', SmrSession::getNewHREF($container));
