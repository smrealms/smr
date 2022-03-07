<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame\Blackjack;

use Exception;
use PHPUnit\Framework\TestCase;
use Smr\Blackjack\Deck;

/**
 * @covers Smr\Blackjack\Deck
 */
class DeckTest extends TestCase {

	public function test_drawCard(): void {
		// test drawing a random card
		srand(123); // set rand seed
		$deck = new Deck();
		self::assertSame(6, $deck->drawCard()->getCardID());
	}

	public function test_drawing_from_empty_deck(): void {
		$deck = new Deck();
		// first we need to exhaust the deck of cards
		for ($i = 0; $i < Deck::MAX_CARDS; $i++) {
			$deck->drawCard();
		}
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No cards left to draw from this deck!');
		$deck->drawCard();
	}

}
