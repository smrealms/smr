<?php declare(strict_types=1);

namespace SmrTest\lib\Blackjack;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Smr\Blackjack\Card;

#[CoversClass(Card::class)]
class CardTest extends TestCase {

	public function test_getCardID(): void {
		// should be the same as the input cardID
		$cardID = 7;
		$card = new Card($cardID);
		self::assertSame($cardID, $card->getCardID());
	}

	public function test_same_card_from_different_decks(): void {
		// spot check that the first card in each deck is the same
		$card1 = new Card(0);
		$card2 = new Card(52);
		self::assertSame($card1->getRankName(), $card2->getRankName());
		self::assertSame($card1->getSuitName(), $card2->getSuitName());
	}

	public function test_isAce(): void {
		// check if each card in a deck is an ace
		$aceCardIDs = [0, 13, 26, 39];
		for ($cardID = 0; $cardID < 52; $cardID++) {
			$card = new Card($cardID);
			self::assertSame(in_array($cardID, $aceCardIDs), $card->isAce());
		}
	}

	#[DataProvider('card_details_provider')]
	public function test_card_details(int $cardID, string $rankName, string $suitName, int $value): void {
		// check various details of a card
		$card = new Card($cardID);
		self::assertSame($rankName, $card->getRankName());
		self::assertSame($suitName, $card->getSuitName());
		self::assertSame($value, $card->getValue());
	}

	/**
	 * @return array<array{int, string, string, int}>
	 */
	public static function card_details_provider(): array {
		// spot check a handful of cards
		return [
			[0, 'A', 'hearts', 11],
			[23, 'J', 'clubs', 10],
			[37, 'Q', 'diamonds', 10],
			[51, 'K', 'spades', 10],
			[18, '6', 'clubs', 6],
		];
	}

}
