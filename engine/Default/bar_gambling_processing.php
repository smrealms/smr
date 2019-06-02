<?php

// blackjack
$message = '';

$max_cards = PlayingCard::MAX_CARDS;

/**
 * Draw a valid card given the cards that are already drawn.
 */
function draw_card($currCards) {
	$newCard = new PlayingCard();
	//no cards twice
	while (in_array($newCard, $currCards)) {
		$newCard = new PlayingCard();
	}
	return $newCard;
}

/**
 * Get the total value of a deck.
 */
function get_value($deck) {
	//this function used to find the value of a player's/bank's cards
	//if this is just one card push it into an array so we can run the func
	if (!is_array($deck)) {
		$deck = array($deck);
	}
	$curr_aces = 0;
	$return_val = 0;
	foreach ($deck as $card) {
		if ($card->isAce()) {
			$curr_aces += 1;
		}
		$return_val += $card->getValue();
	}
	while ($return_val > 21 && $curr_aces > 0) {
		//if we have aces and > 21 score we subtract to make it a 1 instead of 11
		$return_val -= 10;
		$curr_aces -= 1;
	}
	return $return_val;
}

function create_card($card, $show) {
	//only display what the card really is if they want to
	$card_height = 100;
	$card_width = 125;

	$suit = $card->getSuitName();
	$card_name = $card->getRankName();

	$return = ('<td>');
	//lets try and echo cards
	$return .= ('<table style="border:1px solid green"><tr><td><table><tr><td valign=top class="left" height=' . $card_height . ' width=' . $card_width . '>');
	if ($show) {
		$return .= ('<h1>' . $card_name . '<img src="images/' . $suit . '.gif"></h1></td></tr>');
	} else {
		$return .= ('</td></tr>');
	}
	$return .= ('<tr><td valign=bottom class="right" height=' . $card_height . ' width=' . $card_width . '>');
	if ($show) {
		$return .= ('<h1><img src="images/' . $suit . '.gif">' . $card_name . '</h1></td></tr></table>');
	} else {
		$return .= ('</td></tr></table>');
	}
	$return .= ('</td></tr></table></td>');
	return $return;
}

function check_for_win($ai_card, $player_card) {
	$comp = get_value($ai_card);
	$play = get_value($player_card);

	//does the player win
	if (sizeof($player_card) == 2 && $play == 21) {
		return 'bj';
	} elseif ($play > $comp && $comp <= 21 && $play <= 21) {
		return 'yes';
	} elseif ($play == $comp && $comp <= 21) {
		return 'tie';
	} elseif ($comp > 21) {
		return 'yes';
	} else {
		return 'no';
	}
}

if (isset($var['player_does'])) {
	$do = $var['player_does'];
} else {
	$do = 'nothing';
}
//new game if $do == nothing
if ($do == 'nothing') {
	if (isset($var['bet'])) {
		$bet = $var['bet'];
	} else {
		$bet = $_REQUEST['bet'];
	}
	if (!is_numeric($bet)) {
		create_error('Numbers only!');
	}
	$bet = round($bet);
	if ($player->getCredits() < $bet) {
		create_error('Not even enough to play BlackJack...you need to trade!');
	}
	if ($bet == 0) {
		create_error('We don\'t want you here if you don\'t want to play with cash!');
	}
	if ($bet > 100 && $player->getNewbieTurns() > 0) {
		create_error('Sorry.  According to Galactic Laws we can only play with up to 100 credits while under newbie protection.');
	}
	if ($bet > 10000) {
		create_error('Sorry.  According to Galactic Laws we can only play with up to 10,000 credits');
	}
	if ($bet < 0) {
		create_error('Yeah...we are gonna give you money to play us! GREAT IDEA!!');
	}
	$player->decreaseCredits($bet);
	$player->update();

	//first we deal some cards...player,ai,player,ai
	if (isset($var['cards'])) {
		$cards = $var['cards'];
	}
	if (empty($cards)) {
		$cards = array();
	}
	$player_curr_card = draw_card($cards);
	$player_card[] = $player_curr_card;
	$cards[] = $player_curr_card;
	if (sizeof($cards) >= $max_cards) {
		$cards = array();
	}
	$ai_curr_card = draw_card($cards);
	$ai_card[] = $ai_curr_card;
	$cards[] = $ai_curr_card;
	if (sizeof($cards) >= $max_cards) {
		$cards = array();
	}
	$player_curr_card = draw_card($cards);
	$player_card[] = $player_curr_card;
	$cards[] = $player_curr_card;
	if (sizeof($cards) >= $max_cards) {
		$cards = array();
	}
	$ai_curr_card = draw_card($cards);
	$ai_card[] = $ai_curr_card;
	$cards[] = $ai_curr_card;
	if (sizeof($cards) >= $max_cards) {
		$cards = array();
	}
	//find a play_val variable in case they get bj first hand...lucky
	$play_val = get_value($player_card);
}
if (isset($var['cards']) && !isset($cards)) {
	$cards = $var['cards'];
}
if (isset($var['bet'])) {
	$bet = $var['bet'];
}
if (isset($var['player_card'])) {
	$player_card = $var['player_card'];
	$ai_card = $var['ai_card'];
	$play_val = $var['player_val'];
}
if ($do == 'HIT') {
	$player_curr_card = draw_card($cards);
	$player_card[] = $player_curr_card;
	$cards[] = $player_curr_card;
	if (sizeof($cards) >= $max_cards) {
		$cards = array();
	}
	$play_val = get_value($player_card);
}

//only display if we wont display later..
if ($do != 'STAY' && get_value($player_card) != 21) {
	//heres the AIs cards
	$i = 1;
	if ((get_value($ai_card) == 21 && sizeof($ai_card) == 2) ||
	    (get_value($player_card) > 21 && get_value($ai_card) <= 21)) {
		$message .= ('<h1 class="red center">Bank Wins</h1>');
	}
	$message .= ('<div class="center">Bank\'s Cards are</div><br /><table class="center"><tr>');
	foreach ($ai_card as $key => $value) {
		if ($key == 0) {
			//do we need a new row?
			if ($i == 4 || $i == 7 || $i == 10) {
				$message .= ('</tr><tr>');
			}
			$message .= create_card($value, TRUE);
			$curr_ai_card = array();
			$curr_ai_card[] = $value;
			//get curr val of this card...for the at least part
			$ai_val = get_value($curr_ai_card);
			$i++;
		} else {
			//lets try and echo cards
			//new row?
			if ($i == 4 || $i == 7 || $i == 10) {
				$message .= ('</tr><tr>');
			}
			if (get_value($ai_card) == 21 || get_value($player_card) >= 21) {
				$message .= create_card($value, TRUE);
			} else {
				$message .= create_card($value, FALSE);
			}
			$i++;
		}
	}

	$message .= ('</tr></table>');
	if (get_value($ai_card) == 21 && sizeof($ai_card) == 2) {
		$message .= ('<div class="center">Bank has BLACKJACK!</div><br />');
		$win = 'no';
	} elseif (get_value($player_card) >= 21) {
		$message .= ('<div class="center">Bank has ' . get_value($ai_card) . '</div><br />');
	} else {
		$message .= ('<div class="center">Bank has at least ' . $ai_val . '</div><br />');
	}
}

if ($do == 'STAY' || get_value($player_card) == 21) {
	//heres the Banks cards
	$i = 1;

	if (!(sizeof($player_card) == 2 && get_value($player_card) == 21)) {
		while (get_value($ai_card) < 17) {
			$ai_curr_card = draw_card($cards);
			$ai_card[] = $ai_curr_card;
			$cards[] = $ai_curr_card;
			if (sizeof($cards) >= $max_cards) {
				$cards = array();
			}
		}
	}
	$win = check_for_win($ai_card, $player_card);
	if ($win == 'yes' || $win == 'bj') {
		$message .= ('<h1 class="green center">You Win</h1>');
	} elseif ($win == 'tie') {
		$message .= ('<h1 class="yellow center">TIE Game</h1>');
	} else {
		$message .= ('<h1 class="red center">Bank Wins</h1>');
	}
	$message .= ('<div class="center">Bank\'s Cards are</div><br /><table class="center"><tr>');
	foreach ($ai_card as $key => $value) {
		//now row?
		if ($i == 4 || $i == 7 || $i == 10) {
			$message .= ('</tr><tr>');
		}
		$message .= create_card($value, TRUE);
		$i++;
	}
	$message .= ('</tr></table><div class="center">');
	if (get_value($ai_card) > 21) {
		$message .= ('Bank <span class="red"><b>BUSTED</b></span><br /><br />');
	} else {
		$message .= ('Bank has ' . get_value($ai_card) . '<br /><br />');
	}
	$message .= ('</div>');
}
$message .= ('<hr style="border:1px solid green;width:50%" noshade>');
$i = 1;

$val1 = get_value($player_card);

$message .= ('<div class="center">Your Cards are</div><br /><table class="center"><tr>');
foreach ($player_card as $key => $value) {
	if ($i == 4 || $i == 7 || $i == 10) {
		$message .= ('</tr><tr>');
	}
	$message .= create_card($value, TRUE);
	$i++;
}
$message .= ('</tr></table>');

$message .= ('<div class="center">You have a total of ' . get_value($player_card) . ' </div><br />');
//check for win
if ($do == 'STAY') {
	$win = check_for_win($ai_card, $player_card);
}

$container = create_container('bar_gambling_processing.php');
transfer('LocationID');
$container['cards'] = $cards;
$container['bet'] = $bet;

$message .= ('<div class="center">');
if (get_value($player_card) > 21) {
	$message .= ('You have <span class="red"><b>BUSTED</b></span>');
	$player->increaseHOF($bet, array('Blackjack', 'Money', 'Lost'), HOF_PUBLIC);
	$player->increaseHOF(1, array('Blackjack', 'Results', 'Lost'), HOF_PUBLIC);
	$message .= create_echo_form($container);
	$message .= create_submit('Play Some More ($' . $bet . ')');
	$message .= ('</form>');
	$message .= ('</div>');
} else if (!isset($win) && get_value($player_card) < 21) {
	$container['player_card'] = $player_card;
	$container['player_does'] = 'HIT';
	$container['ai_card'] = $ai_card;
	$container['player_val'] = $val1;
	$message .= create_echo_form($container);
	$message .= create_submit('HIT');
	$message .= ('<br /><small><br /></small></form>');
	$container['player_does'] = 'STAY';
	$message .= create_echo_form($container);
	$message .= create_submit('STAY');
	$message .= ('</form></div>');
} else if (isset($win)) {
	//we have a winner...but who!
	if ($win == 'bj') {
		$player->increaseCredits($bet * 2.5);
		$stat = ($bet * 2.5) - $bet;
		$player->update();
		$player->increaseHOF($stat, array('Blackjack', 'Money', 'Won'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Won'), HOF_PUBLIC);
		$message .= ('You have won $' . number_format($bet * 2.5) . ' credits!');
	} elseif ($win == 'yes') {
		$player->increaseCredits($bet * 2);
		$stat = ($bet * 2) - $bet;
		$player->update();
		$player->increaseHOF($stat, array('Blackjack', 'Money', 'Won'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Won'), HOF_PUBLIC);
		$message .= ('You have won $' . number_format($bet * 2) . ' credits!');
	} elseif ($win == 'tie') {
		$player->increaseCredits($bet);
		$player->update();
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Draw'), HOF_PUBLIC);
		$message .= ('You have won back your $' . number_format($bet) . ' credits.');
	} else {
		$player->increaseHOF($bet, array('Blackjack', 'Money', 'Lost'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Lost'), HOF_PUBLIC);
	}
	$message .= create_echo_form($container);
	$message .= create_submit('Play Some More ($' . $bet . ')');
	$message .= ('</form>');
	$message .= ('</div>');
} elseif ($val1 == 21) {
	if (get_value($ai_card) != 21) {
		if (sizeof($player_card) == 2) {
			$winnings = 2.5;
		} else {
			$winnings = 2;
		}
		$player->increaseCredits($bet * $winnings);
		$stat = ($bet * $winnings) - $bet;
		$player->update();
		$player->increaseHOF($stat, array('Blackjack', 'Money', 'Win'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Win'), HOF_PUBLIC);
		$message .= ('You have won $' . number_format($bet * $winnings) . ' credits!');
	} else if (sizeof($ai_card) > 2) {
		$winnings = 1;
		$player->increaseCredits($bet * $winnings);
		$stat = ($bet * $winnings) - $bet;
		$player->update();
		$player->increaseHOF($stat, array('Blackjack', 'Money', 'Win'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Win'), HOF_PUBLIC);
		$message .= ('You have won back your $' . number_format($bet * $winnings) . ' credits!');
	} else {
		//AI has BJ already...sorry
		$player->increaseHOF($bet, array('Blackjack', 'Money', 'Lost'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Lost'), HOF_PUBLIC);
	}
	$message .= create_echo_form($container);
	$message .= create_submit('Play Some More ($' . $bet . ')');
	$message .= ('</form>');
	$message .= ('</div>');
}

$container = create_container('skeleton.php', 'bar_gambling_bet.php');
transfer('LocationID');
$container['message'] = $message;
$container['AllowAjax'] = false;
forward($container);
