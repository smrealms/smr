<?php declare(strict_types=1);

// blackjack
$message = '';

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

function check_for_win($dealerHand, $playerHand) {
	$comp = $dealerHand->getValue();
	$play = $playerHand->getValue();

	//does the player win
	if ($playerHand->hasBlackjack()) {
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

$deck = $var['deck'] ?? new \Blackjack\Deck();
$playerHand = $var['player_hand'] ?? new \Blackjack\Hand();
$dealerHand = $var['dealer_hand'] ?? new \Blackjack\Hand();

if (isset($var['player_does'])) {
	$do = $var['player_does'];
} else {
	$do = 'nothing';
}
//new game if $do == nothing
if ($do == 'nothing') {
	$bet = Request::getVarInt('bet');
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

	//first we deal some cards...player,ai,player,ai
	$playerHand->drawCard($deck);
	$dealerHand->drawCard($deck);
	$playerHand->drawCard($deck);
	$dealerHand->drawCard($deck);
}

if (isset($var['bet'])) {
	$bet = $var['bet'];
}

if ($do == 'HIT') {
	$playerHand->drawCard($deck);
}

//only display if we wont display later..
if ($do != 'STAY' && $playerHand->getValue() != 21) {
	//heres the AIs cards
	$i = 1;
	if ($dealerHand->hasBlackjack() ||
	    ($playerHand->getValue() > 21 && $dealerHand->getValue() <= 21)) {
		$message .= ('<h1 class="red center">Bank Wins</h1>');
	}
	$message .= ('<div class="center">Bank\'s Cards are</div><br /><table class="center"><tr>');
	foreach ($dealerHand->getCards() as $key => $card) {
		if ($key == 0) {
			//do we need a new row?
			if ($i == 4 || $i == 7 || $i == 10) {
				$message .= ('</tr><tr>');
			}
			$message .= create_card($card, TRUE);
			//get curr val of this card...for the at least part
			$ai_val = $card->getValue();
		} else {
			//lets try and echo cards
			//new row?
			if ($i == 4 || $i == 7 || $i == 10) {
				$message .= ('</tr><tr>');
			}
			if ($dealerHand->getValue() == 21 || $playerHand->getValue() >= 21) {
				$message .= create_card($card, TRUE);
			} else {
				$message .= create_card($card, FALSE);
			}
		}
		$i++;
	}

	$message .= ('</tr></table>');
	if ($dealerHand->hasBlackjack()) {
		$message .= ('<div class="center">Bank has BLACKJACK!</div><br />');
		$win = 'no';
	} elseif ($playerHand->getValue() >= 21) {
		$message .= ('<div class="center">Bank has ' . $dealerHand->getValue() . '</div><br />');
	} else {
		$message .= ('<div class="center">Bank has at least ' . $ai_val . '</div><br />');
	}
}

if ($do == 'STAY' || $playerHand->getValue() == 21) {
	//heres the Banks cards
	$i = 1;

	if (!$playerHand->hasBlackjack()) {
		while ($dealerHand->getValue() < 17) {
			$dealerHand->drawCard($deck);
		}
	}
	$win = check_for_win($dealerHand, $playerHand);
	if ($win == 'yes' || $win == 'bj') {
		$message .= ('<h1 class="green center">You Win</h1>');
	} elseif ($win == 'tie') {
		$message .= ('<h1 class="yellow center">TIE Game</h1>');
	} else {
		$message .= ('<h1 class="red center">Bank Wins</h1>');
	}
	$message .= ('<div class="center">Bank\'s Cards are</div><br /><table class="center"><tr>');
	foreach ($dealerHand->getCards() as $key => $card) {
		//now row?
		if ($i == 4 || $i == 7 || $i == 10) {
			$message .= ('</tr><tr>');
		}
		$message .= create_card($card, TRUE);
		$i++;
	}
	$message .= ('</tr></table><div class="center">');
	if ($dealerHand->getValue() > 21) {
		$message .= ('Bank <span class="red"><b>BUSTED</b></span><br /><br />');
	} else {
		$message .= ('Bank has ' . $dealerHand->getValue() . '<br /><br />');
	}
	$message .= ('</div>');
}
$message .= ('<hr style="border:1px solid green;width:50%" noshade>');
$i = 1;

$val1 = $playerHand->getValue();

$message .= ('<div class="center">Your Cards are</div><br /><table class="center"><tr>');
foreach ($playerHand->getCards() as $key => $card) {
	if ($i == 4 || $i == 7 || $i == 10) {
		$message .= ('</tr><tr>');
	}
	$message .= create_card($card, TRUE);
	$i++;
}
$message .= ('</tr></table>');

if ($playerHand->hasBlackjack()) {
	$message .= '<div class="center">You have BLACKJACK!</div><br />';
} else {
	$message .= ('<div class="center">You have a total of ' . $playerHand->getValue() . ' </div><br />');
}

//check for win
if ($do == 'STAY') {
	$win = check_for_win($dealerHand, $playerHand);
}

$container = create_container('bar_gambling_processing.php');
transfer('LocationID');
$container['bet'] = $bet;

$message .= ('<div class="center">');
if ($playerHand->getValue() > 21) {
	$message .= ('You have <span class="red"><b>BUSTED</b></span>');
	$player->increaseHOF($bet, array('Blackjack', 'Money', 'Lost'), HOF_PUBLIC);
	$player->increaseHOF(1, array('Blackjack', 'Results', 'Lost'), HOF_PUBLIC);
	$message .= '<p><a class="submitStyle" href="' . SmrSession::getNewHREF($container) . '">Play Some More ($' . $bet . ')</a></p>';
	$message .= ('</div>');
} elseif (!isset($win) && $playerHand->getValue() < 21) {
	$container['deck'] = $deck;
	$container['player_hand'] = $playerHand;
	$container['player_does'] = 'HIT';
	$container['dealer_hand'] = $dealerHand;
	$message .= '<form method="POST" action="' . SmrSession::getNewHREF($container) . '">';
	$message .= '<input type="submit" name="action" value="HIT" />';
	$message .= ('<br /><small><br /></small></form>');
	$container['player_does'] = 'STAY';
	$message .= '<form method="POST" action="' . SmrSession::getNewHREF($container) . '">';
	$message .= '<input type="submit" name="action" value="STAY" />';
	$message .= ('</form></div>');
} elseif (isset($win)) {
	//we have a winner...but who!
	if ($win == 'bj') {
		$winnings = IFloor($bet * 2.5);
		$player->increaseCredits($winnings);
		$stat = $winnings - $bet;
		$player->increaseHOF($stat, array('Blackjack', 'Money', 'Won'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Won'), HOF_PUBLIC);
		$message .= ('You have won $' . number_format($winnings) . ' credits!');
	} elseif ($win == 'yes') {
		$winnings = $bet * 2;
		$player->increaseCredits($winnings);
		$stat = $winnings - $bet;
		$player->increaseHOF($stat, array('Blackjack', 'Money', 'Won'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Won'), HOF_PUBLIC);
		$message .= ('You have won $' . number_format($winnings) . ' credits!');
	} elseif ($win == 'tie') {
		$player->increaseCredits($bet);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Draw'), HOF_PUBLIC);
		$message .= ('You have won back your $' . number_format($bet) . ' credits.');
	} else {
		$player->increaseHOF($bet, array('Blackjack', 'Money', 'Lost'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Lost'), HOF_PUBLIC);
	}
	$message .= '<p><a class="submitStyle" href="' . SmrSession::getNewHREF($container) . '">Play Some More ($' . $bet . ')</a></p>';
	$message .= ('</div>');
} elseif ($playerHand->getValue() == 21) {
	if ($dealerHand->getValue() != 21) {
		if ($playerHand->getNumCards() == 2) {
			$multiplier = 2.5;
		} else {
			$multiplier = 2;
		}
		$winnings = IFloor($bet * $multiplier);
		$player->increaseCredits($winnings);
		$stat = $winnings - $bet;
		$player->increaseHOF($stat, array('Blackjack', 'Money', 'Win'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Win'), HOF_PUBLIC);
		$message .= ('You have won $' . number_format($winnings) . ' credits!');
	} elseif ($dealerHand->getNumCards() > 2) {
		$winnings = $bet;
		$player->increaseCredits($winnings);
		$stat = $winnings - $bet;
		$player->increaseHOF($stat, array('Blackjack', 'Money', 'Win'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Win'), HOF_PUBLIC);
		$message .= ('You have won back your $' . number_format($winnings) . ' credits!');
	} else {
		//AI has BJ already...sorry
		$player->increaseHOF($bet, array('Blackjack', 'Money', 'Lost'), HOF_PUBLIC);
		$player->increaseHOF(1, array('Blackjack', 'Results', 'Lost'), HOF_PUBLIC);
	}
	$message .= '<p><a class="submitStyle" href="' . SmrSession::getNewHREF($container) . '">Play Some More ($' . $bet . ')</a></p>';
	$message .= ('</div>');
}

$player->update();
$container = create_container('skeleton.php', 'bar_gambling_bet.php');
transfer('LocationID');
$container['message'] = $message;
$container['AllowAjax'] = false;
forward($container);
