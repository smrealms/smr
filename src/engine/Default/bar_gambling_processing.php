<?php declare(strict_types=1);

use Smr\Blackjack\Card;
use Smr\Blackjack\Hand;
use Smr\Blackjack\Result;
use Smr\Blackjack\Table;
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

/** @var \Smr\Blackjack\Table $table */
$table = $var['table'] ?? new Table();

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
}

// Add cards to the player's hand
if ($do == 'HIT') {
	$table->playerHits();
}

// Check if the game has ended
$gameEnded = ($do == 'STAY' || $table->gameOver());

$resultMsg = '';
$winningsMsg = '';
if ($gameEnded) {
	// Add cards to the dealer's hand (if necessary)
	$table->dealerHitsUntil(17);

	// Construct the result message
	$result = $table->getPlayerResult();
	$resultMsg = match ($result) {
		Result::Win, Result::Blackjack => '<h1 class="green">You Win</h1>',
		Result::Tie => '<h1 class="yellow">TIE Game</h1>',
		Result::Lose => '<h1 class="red">Dealer Wins</h1>',
	};

	// Process winnings and HoF stats
	if ($result == Result::Win || $result == Result::Blackjack) {
		$multiplier = $result == Result::Blackjack ? 2.5 : 2;
		$winnings = IFloor($bet * $multiplier);
		$player->increaseCredits($winnings);
		$stat = $winnings - $bet;
		$player->increaseHOF($stat, ['Blackjack', 'Money', 'Won'], HOF_PUBLIC);
		$player->increaseHOF(1, ['Blackjack', 'Results', 'Won'], HOF_PUBLIC);
		$winningsMsg = 'You have won $' . number_format($winnings) . ' credits!';
	} elseif ($result == Result::Tie) {
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

// Display the dealer side
$dealerHand = $table->dealerHand;
$message .= '<div>Dealer\'s Cards are</div><br />';
$message .= display_hand($dealerHand, $gameEnded);

$result = [];
if ($gameEnded) {
	$result[] = 'Dealer has a total of ' . $dealerHand->getValue();
} else {
	// Only the dealer's first card is visible to the player
	$result[] = 'Dealer has at least ' . $dealerHand->getCards()[0]->getValue();
}
if ($dealerHand->hasBlackjack()) {
	$result[] = 'Dealer has BLACKJACK!';
} elseif ($dealerHand->hasBusted()) {
	$result[] = 'Dealer <span class="red"><b>BUSTED</b></span>';
}
$message .= '<div>' . implode('<br />', $result) . '</div><br />';

// Separator
$message .= '<hr style="border:1px solid green;width:50%" noshade>';

// Display the player side
$playerHand = $table->playerHand;
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
	$container['table'] = $table;
	$container['player_does'] = 'HIT';
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
