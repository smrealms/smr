<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\Blackjack\Card;
use Smr\Blackjack\Hand;
use Smr\Blackjack\Result;
use Smr\Blackjack\Table;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

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
		if ($key > 0 && $key % 3 === 0) {
			$html .= '</tr><tr>';
		}
		$showCard = ($key === 0 || $revealHand === true);
		$html .= display_card($card, $showCard);
	}
	$html .= '</tr></table>';
	return $html;
}

class PlayBlackjack extends PlayerPage {

	public function __construct(
		private readonly int $locationID,
		private readonly Table $table,
		private readonly bool $gameEnded,
		private readonly int $bet,
		private readonly string $winningsMsg,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'BlackJack';
		Menu::bar($this->locationID);

		$table = $this->table;
		$gameEnded = $this->gameEnded;

		$resultMsg = '';
		if ($gameEnded) {
			// Construct the result message
			$result = $table->getPlayerResult();
			$resultMsg = match ($result) {
				Result::Win, Result::Blackjack => '<h1 class="green">You Win</h1>',
				Result::Tie => '<h1 class="yellow">TIE Game</h1>',
				Result::Lose => '<h1 class="red">Dealer Wins</h1>',
			};
		}

		// Display the dealer side
		$dealerHand = $table->dealerHand;

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

		// Display the player side
		$playerHand = $table->playerHand;

		$result = ['You have a total of ' . $playerHand->getValue()];
		if ($playerHand->hasBlackjack()) {
			$result[] = 'You have BLACKJACK!';
		} elseif ($playerHand->hasBusted()) {
			$result[] = 'You have <span class="red"><b>BUSTED</b></span>';
		}

		// Create action buttons
		if ($gameEnded) {
			$betHREF = new PlayBlackjackProcessor(
				locationID: $this->locationID,
				action: 'new game',
				bet: $this->bet,
			)->href();
			$hitHREF = null;
			$stayHREF = null;
		} else {
			$betHREF = null;
			$hitHREF = new PlayBlackjackProcessor(
				locationID: $this->locationID,
				action: 'HIT',
				table: $table,
				bet: $this->bet,
			)->href();

			$stayHREF = new PlayBlackjackProcessor(
				locationID: $this->locationID,
				action: 'STAY',
				table: $table,
				bet: $this->bet,
			)->href();
		}

		$template->pageRenderer = fn() => PlayBlackjackRenderer::render(
			ResultMsg: $resultMsg,
			DealerHand: display_hand($dealerHand, $gameEnded),
			DealerStatus: implode('<br />', $result),
			PlayerHand: display_hand($playerHand, true),
			PlayerStatus: implode('<br />', $result),
			Winnings: $this->winningsMsg,
			BetHREF: $betHREF,
			Bet: $this->bet,
			HitHREF: $hitHREF,
			StayHREF: $stayHREF,
		);
	}

}
