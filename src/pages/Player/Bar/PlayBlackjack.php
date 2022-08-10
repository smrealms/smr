<?php declare(strict_types=1);

use Smr\Blackjack\Card;
use Smr\Blackjack\Hand;
use Smr\Blackjack\Result;

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

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$player = $session->getPlayer();

		$template->assign('PageTopic', 'BlackJack');
		Menu::bar();

		/** @var \Smr\Blackjack\Table $table */
		$table = $var['table'];

		$gameEnded = $var['gameEnded'];

		$resultMsg = '';
		$winningsMsg = '';
		if ($gameEnded) {
			// Construct the result message
			$result = $table->getPlayerResult();
			$resultMsg = match ($result) {
				Result::Win, Result::Blackjack => '<h1 class="green">You Win</h1>',
				Result::Tie => '<h1 class="yellow">TIE Game</h1>',
				Result::Lose => '<h1 class="red">Dealer Wins</h1>',
			};

			$winningsMsg = $var['winningsMsg'];
		}
		$template->assign('ResultMsg', $resultMsg);

		// Display the dealer side
		$dealerHand = $table->dealerHand;
		$template->assign('DealerHand', display_hand($dealerHand, $gameEnded));

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
		$template->assign('DealerStatus', implode('<br />', $result));

		// Display the player side
		$playerHand = $table->playerHand;
		$template->assign('PlayerHand', display_hand($playerHand, true));

		$result = ['You have a total of ' . $playerHand->getValue()];
		if ($playerHand->hasBlackjack()) {
			$result[] = 'You have BLACKJACK!';
		} elseif ($playerHand->hasBusted()) {
			$result[] = 'You have <span class="red"><b>BUSTED</b></span>';
		}
		$template->assign('PlayerStatus', implode('<br />', $result));

		// Create action buttons
		$container = Page::create('bar_gambling_processing.php');
		$container->addVar('LocationID');
		$container->addVar('bet');

		if ($gameEnded) {
			$template->assign('Winnings', $var['winningsMsg']);
			$template->assign('BetHREF', $container->href());
			$template->assign('Bet', $var['bet']);
		} else {
			$container['table'] = $table;
			$container['player_does'] = 'HIT';
			$template->assign('HitHREF', $container->href());

			$container['player_does'] = 'STAY';
			$template->assign('StayHREF', $container->href());
		}
