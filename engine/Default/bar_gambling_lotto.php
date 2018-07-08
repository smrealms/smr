<?php
require_once(get_file_loc('bar.functions.inc'));
checkForLottoWinner($player->getGameID());
$lottoInfo = getLottoInfo($player->getGameID());
$template->assign('LottoInfo', $lottoInfo);

$container = create_container('skeleton.php', 'bar_main.php');
$container['script'] = 'bar_gambling_processing.php';
$container['action'] = 'process';
$template->assign('BuyTicketHREF', SmrSession::getNewHREF($container));
