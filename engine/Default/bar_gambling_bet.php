<?php
if(isset($var['message'])) {
	$template->assign('Message', $var['message']);
	return;
}

if ($player->hasNewbieTurns()) {
	$maxBet = 100;
	$maxBetMsg = 'Since you have newbie protection, your max bet is '.$maxBet.'.';
} else {
	$maxBet = 10000;
	$maxBetMsg = 'Max bet is '.$maxBet.'.';
}
$template->assign('MaxBet', $maxBet);
$template->assign('MaxBetMsg', $maxBetMsg);

$container = create_container('skeleton.php', 'bar_main.php');
$container['script'] = 'bar_gambling_processing.php';
$container['action'] = 'blackjack';
$template->assign('PlayHREF', SmrSession::getNewHREF($container));
