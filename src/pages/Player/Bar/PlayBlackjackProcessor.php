<?php declare(strict_types=1);

use Smr\Blackjack\Result;
use Smr\Blackjack\Table;
use Smr\Request;

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

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

$winningsMsg = '';
if ($gameEnded) {
	// Add cards to the dealer's hand (if necessary)
	$table->dealerHitsUntil(17);

	// Process winnings and HoF stats
	$result = $table->getPlayerResult();
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

$player->update();
$container = Page::create('bar_gambling.php');
$container->addVar('LocationID');
$container['bet'] = $bet;
$container['table'] = $table;
$container['gameEnded'] = $gameEnded;
$container['winningsMsg'] = $winningsMsg;
$container->go();
