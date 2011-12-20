<?php
if(isset($var['message'])) {
	$PHP_OUTPUT.=$var['message'];
	return;
}

$PHP_OUTPUT.=('<div align=center>How much do you want to bet? ');
if ($player->getNewbieTurns() > 0) {
	$value = 100;
	$PHP_OUTPUT.=('(Since you have newbie protection max bet is '.$value.')<br />');
} else {
	$value = 10000;
	$PHP_OUTPUT.=('(Max bet is '.$value.')<br />');
}

$container = create_container('skeleton.php', 'bar_main.php');
$container['script'] = 'bar_gambling_processing.php';
$container['action'] = 'blackjack';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<input type="text" value="'.$value.'" name="bet" id="InputFields" style="width:100px;" class="center">&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Play the Game');
$PHP_OUTPUT.=('</form></div>');
$PHP_OUTPUT.=('<br /><br />');
$PHP_OUTPUT.=('Don\'t know how about BlackJack?  Not a problem...Check out the following!<br />');
$PHP_OUTPUT.=('<h1>HISTORY</h1>Blackjack comes from a family of games that includes baccarat, vingt-et-un and seven-and-a-half. Blackjack gained popularity in the United States during World War I as weary troops looked for interesting pastimes. Also known as Twenty-One, the game made fortunes for a number of expert "card counters" in the 1960s. These experts were able to identify points in the game when players temporarily had an edge over the house, at which time they would greatly increase the size of their bets. Casinos have since made it more difficult for players to win by adding decks and shuffling more often, but it\'s still a game where skillful players have a relatively good chance for a winning session.<br /><br />');
$PHP_OUTPUT.=('<h1>BASICS</h1>You compete against the dealer. The object of the game is to have a higher point total than the dealer without going over 21. Each ace counts as either 1 point or 11 points, face cards (kings, queens, jacks) count 10 points each, and all other cards (2 through 9) count their face value.<br /><br />');
$PHP_OUTPUT.=('<h1>BETTING</h1>Before the deal, you place you bet. In order to place a bet you enter the amount you wish to bet and then click \'Play the Game\'. The maximum bet per hand is always $'.$value.'.<br /><br />');
$PHP_OUTPUT.=('<h1>THE PLAY</h1>The dealer deals two cards to you and two cards to himself. One of the dealer\'s cards is dealt faceup and the other is facedown. After the deal, the dealer "asks" you whether you want an additional card. A player may "Stay" -- play just the two cards originally dealt or may "Hit"-- take another card. After being dealt an additional card, you may stop or may take still another card. You may take as many cards as you want, but as soon as your total exceeds 21, you lose.<br /><br />');
$PHP_OUTPUT.=('After you have drawn, the dealer\'s remaining card is exposed. Under SMR rules, a dealer with a total less than 17 must "hit" (take a card); with 17 or more, dealer must stand.<br /><br />');
$PHP_OUTPUT.=('If dealer "busts" by going over 21, any players still in the game win. Otherwise, players with totals higher than the dealer win, while players with totals less than the dealer lose. In case of a tie, or "push" the player\'s bet is returned (no money changes hands).<br /><br />');
$PHP_OUTPUT.=('If your or the dealer\'s first two cards total 21 (an ace and a 10 or face card), the holding is known as a blackjack. A player with blackjack is paid extra--two and a half times the original bet--unless dealer also has blackjack, in which case the player loses.<br /><br />');

?>