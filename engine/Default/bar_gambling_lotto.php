<?php
require_once(get_file_loc('bar.functions.inc'));
checkForLottoWinner($player->getGameID());
$lottoInfo = getLottoInfo($player->getGameID());

//find the time remaining in this jackpot. (which is 2 days from the first purchased ticket)
$days = floor($lottoInfo['TimeRemaining'] / 60 / 60 / 24);
$lottoInfo['TimeRemaining'] -= $days * 60 * 60 * 24;
$hours = floor($lottoInfo['TimeRemaining'] / 60 / 60);
$lottoInfo['TimeRemaining'] -= $hours * 60 * 60;
$mins = floor($lottoInfo['TimeRemaining'] / 60);
$lottoInfo['TimeRemaining'] -= $mins * 60;
$secs = $lottoInfo['TimeRemaining'];
$lottoInfo['TimeRemaining'] = '<b>'.$days.' Days, '.$hours.' Hours, '.$mins.' Minutes, and '.$secs.' Seconds</b>';
$PHP_OUTPUT.=('<br /><div align="center">Currently '.$lottoInfo['TimeRemaining'].' remain until the winning ticket');
$PHP_OUTPUT.=(' is drawn, and the prize is $' . number_format($lottoInfo['Prize']) . '.<br /><br />');
$PHP_OUTPUT.=('Ahhhh so your interested in the lotto huh?  ');
$PHP_OUTPUT.=('Well here is how it works.  First you will need to buy a ticket, ');
$PHP_OUTPUT.=('they cost $1,000,000 a piece.  Next you need to watch the news.  Once the winning ');
$PHP_OUTPUT.=('lotto ticket is drawn there will be a section in the news with the winner.');
$PHP_OUTPUT.=('  If you win you can come to any bar and claim your prize!');
$PHP_OUTPUT.=('<br /><br />');
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'bar_main.php';
$container['script'] = 'bar_gambling_processing.php';
$container['action'] = 'process';
$PHP_OUTPUT.=create_button($container,'Buy a Ticket ($1,000,000)');
$PHP_OUTPUT.=('</div>');
?>
