<?php declare(strict_types=1);

use Smr\Blackjack\Card;
use Smr\Blackjack\Deck;
use Smr\Blackjack\Hand;
use Smr\Request;

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

function display_card(Card $card, bool $show): string {
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

function display_hand(Hand $hand, bool $revealHand): string {
	$html = '<table class="center"><tr>';
	foreach ($hand->getCards() as $key => $card) {
		//do we need a new row?
		if ($key > 0 && $key % 3 == 0) {
			$html .= '</tr><tr>';
		}
		$showCard = ($key == 0 || $revealHand === true);
		$html .= display_card($card, $showCard);
	}
	$html .= '</tr></table>';
	return $html;
}

function check_for_win(Hand $dealerHand, Hand $playerHand): string {
	//does the player win
	return match (true) {
		$playerHand->hasBusted() => 'no',
		$playerHand->hasBlackjack() => 'bj',
		$playerHand->getValue() == $dealerHand->getValue() => 'tie',
		$playerHand->getValue() > $dealerHand->getValue() => 'yes',
		$dealerHand->hasBusted() => 'yes',
		default => 'no',
	};
}

$deck = $var['deck'] ?? new Deck();
$playerHand = $var['player_hand'] ?? new Hand();
$dealerHand = $var['dealer_hand'] ?? new Hand();

$do = $var['player_does'] ?? 'new game';
$bet = Request::getVarInt('bet');

if ($do == 'new game') {
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
	$playerHand->addCard($deck->drawCard());
	$dealerHand->addCard($deck->drawCard());
	$playerHand->addCard($deck->drawCard());
	$dealerHand->addCard($deck->drawCard());
}

// Add cards to the player's hand
if ($do == 'HIT') {
	$playerHand->addCard($deck->drawCard());
}

// Check if the game has ended
$gameEnded = ($do == 'STAY' || $playerHand->getValue() >= 21 || $dealerHand->getValue() >= 21);

$resultMsg = '';
$winningsMsg = '';
if ($gameEnded) {
	// Add cards to the dealer's hand (if necessary)
	if (!$playerHand->hasBusted() && !$playerHand->hasBlackjack()) {
		while ($dealerHand->getValue() < 17) {
			$dealerHand->addCard($deck->drawCard());
		}
	}

	// Construct the result message
	$win = check_for_win($dealerHand, $playerHand);
	if ($win == 'yes' || $win == 'bj') {
		$resultMsg = '<h1 class="green">You Win</h1>';
	} elseif ($win == 'tie') {
		$resultMsg = '<h1 class="yellow">TIE Game</h1>';
	} else {
		$resultMsg = '<h1 class="red">Bank Wins</h1>';
	}

	// Process winnings and HoF stats
	if ($win == 'bj' || $win == 'yes') {
		$multiplier = $win == 'bj' ? 2.5 : 2;
		$winnings = IFloor($bet * $multiplier);
		$player->increaseCredits($winnings);
		$stat = $winnings - $bet;
		$player->increaseHOF($stat, ['Blackjack', 'Money', 'Won'], HOF_PUBLIC);
		$player->increaseHOF(1, ['Blackjack', 'Results', 'Won'], HOF_PUBLIC);
		$winningsMsg = 'You have won $' . number_format($winnings) . ' credits!';
	} elseif ($win == 'tie') {
		$player->increaseCredits($bet);
		$player->increaseHOF(1, ['Blackjack', 'Results', 'Draw'], HOF_PUBLIC);
		$winningsMsg = 'You have won back your $' . number_format($bet) . ' credits.';
	} else {
		$player->increaseHOF($bet, ['Blackjack', 'Money', 'Lost'], HOF_PUBLIC);
		$player->increaseHOF(1, ['Blackjack', 'Results', 'Lost'], HOF_PUBLIC);
	}
}

// Construct display message
$message = '<div class="center">';
$message .= $resultMsg;

// Display the bank side
$message .= '<div>Bank\'s Cards are</div><br />';
$message .= display_hand($dealerHand, $gameEnded);

$result = [];
if ($gameEnded) {
	$result[] = 'Bank has a total of ' . $dealerHand->getValue();
} else {
	// Only the bank's first card is visible to the player
	$result[] = 'Bank has at least ' . $dealerHand->getCards()[0]->getValue();
}
if ($dealerHand->hasBlackjack()) {
	$result[] = 'Bank has BLACKJACK!';
} elseif ($dealerHand->hasBusted()) {
	$result[] = 'Bank <span class="red"><b>BUSTED</b></span>';
}
$message .= '<div>' . implode('<br />', $result) . '</div><br />';

// Separator
$message .= '<hr style="border:1px solid green;width:50%" noshade>';

// Display the player side
$message .= '<div>Your Cards are</div><br />';
$message .= display_hand($playerHand, true);

$result = ['You have a total of ' . $playerHand->getValue()];
if ($playerHand->hasBlackjack()) {
	$result[] = 'You have BLACKJACK!';
} elseif ($playerHand->hasBusted()) {
	$result[] = 'You have <span class="red"><b>BUSTED</b></span>';
}
$message .= '<div>' . implode('<br />', $result) . '</div><br />';
$message .= $winningsMsg;

// Create action buttons
$container = Page::create('bar_gambling_processing.php');
$container->addVar('LocationID');
$container['bet'] = $bet;

if ($gameEnded) {
	$message .= '<p><a class="submitStyle" href="' . $container->href() . '">Play Some More ($' . $bet . ')</a></p>';
} else {
	$container['deck'] = $deck;
	$container['player_hand'] = $playerHand;
	$container['player_does'] = 'HIT';
	$container['dealer_hand'] = $dealerHand;
	$message .= '<form method="POST" action="' . $container->href() . '">';
	$message .= '<input type="submit" name="action" value="HIT" />';
	$message .= '<br /><small><br /></small></form>';
	$container['player_does'] = 'STAY';
	$message .= '<form method="POST" action="' . $container->href() . '">';
	$message .= '<input type="submit" name="action" value="STAY" />';
	$message .= '</form>';
}
$message .= '</div>';

$player->update();
$container = Page::create('bar_gambling_bet.php');
$container->addVar('LocationID');
$container['message'] = $message;
$container['AllowAjax'] = false;
$container->go();
