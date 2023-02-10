<?php declare(strict_types=1);

namespace SmrTest\lib\Blackjack;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\Blackjack\Card;
use Smr\Blackjack\Hand;

#[CoversClass(Hand::class)]
class HandTest extends TestCase {

	public function test_getValue(): void {
		// test that ace values update properly
		$hand = new Hand();
		$hand->addCard(new Card(0)); // ace of hearts
		self::assertSame(11, $hand->getValue());

		// adding another ace only counts as 1 instead of 11
		$hand->addCard(new Card(13)); // ace of clubs
		self::assertSame(12, $hand->getValue());

		// adding a 10-point card makes both aces count as 1
		$hand->addCard(new Card(12)); // king of hearts
		self::assertSame(12, $hand->getValue());

		// now just add another arbitrary card
		$hand->addCard(new Card(1)); // 2 of hearts
		self::assertSame(14, $hand->getValue());
	}

	public function test_getNumCards(): void {
		// start with an empty hand
		$hand = new Hand();
		self::assertSame(0, $hand->getNumCards());

		// add a card
		$hand->addCard(new Card(0));
		self::assertSame(1, $hand->getNumCards());

		// add another card
		$hand->addCard(new Card(1));
		self::assertSame(2, $hand->getNumCards());
	}

	public function test_getCards(): void {
		// start with an empty hand
		$hand = new Hand();
		self::assertSame([], $hand->getCards());

		// add two cards
		$cards = [new Card(0), new Card(1)];
		foreach ($cards as $card) {
			$hand->addCard($card);
		}
		self::assertSame($cards, $hand->getCards());
	}

	public function test_hasBlackjack(): void {
		// add 2 cards that add to 21 (blackjack)
		$hand = new Hand();
		$hand->addCard(new Card(0)); // ace of hearts
		$hand->addCard(new Card(12)); // king of hearts
		self::assertTrue($hand->hasBlackjack());

		// add 3 cards that add to 21 (not blackjack)
		$hand = new Hand();
		$hand->addCard(new Card(0)); // ace of hearts
		$hand->addCard(new Card(3)); // 4 of hearts
		$hand->addCard(new Card(5)); // 6 of hearts
		self::assertSame(21, $hand->getValue());
		self::assertFalse($hand->hasBlackjack());

		// add 2 cards that do not add to 21 (not blackjack)
		$hand = new Hand();
		$hand->addCard(new Card(0)); // ace of hearts
		$hand->addCard(new Card(8)); // 9 of hearts
		self::assertNotSame(21, $hand->getValue());
		self::assertFalse($hand->hasBlackjack());
	}

	public function test_hasBusted(): void {
		// add 3 cards that add to 22 (busted)
		$hand = new Hand();
		$hand->addCard(new Card(1)); // 2 of hearts
		$hand->addCard(new Card(11)); // queen of hearts
		$hand->addCard(new Card(12)); // king of hearts
		self::assertTrue($hand->hasBusted());

		// add 2 cards that add to 21 (not busted)
		$hand = new Hand();
		$hand->addCard(new Card(0)); // ace of hearts
		$hand->addCard(new Card(12)); // king of hearts
		self::assertFalse($hand->hasBusted());
	}

}
