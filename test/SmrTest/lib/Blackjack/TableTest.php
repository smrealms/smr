<?php declare(strict_types=1);

namespace SmrTest\lib\Blackjack;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\Blackjack\Card;
use Smr\Blackjack\Result;
use Smr\Blackjack\Table;

#[CoversClass(Table::class)]
class TableTest extends TestCase {

	public function test_initial_state(): void {
		// Table constructed with deal=False, hands should be empty
		$table = new Table(false);
		self::assertEmpty($table->playerHand->getCards());
		self::assertEmpty($table->playerHand->getCards());

		// Table constructed with deal=True, hands have 2 cards each
		$table = new Table(true);
		self::assertCount(2, $table->playerHand->getCards());
		self::assertCount(2, $table->playerHand->getCards());
	}

	public function test_playerHits(): void {
		$table = new Table(false);

		// When the player "hits", they should get a card
		$table->playerHits();
		self::assertCount(1, $table->playerHand->getCards());

		// dealer's hand should be unchanged
		self::assertEmpty($table->dealerHand->getCards());
	}

	public function test_dealerHitsUntil(): void {
		$table = new Table(false);

		// Dealer should be given cards until it has >= this value
		$table->dealerHitsUntil(22);
		self::assertGreaterThanOrEqual(22, $table->dealerHand->getValue());

		// player's hand should be unchanged
		self::assertEmpty($table->playerHand->getCards());
	}

	public function test_dealerHitsUntil_do_nothing(): void {
		$table = new Table(false);

		// Give the player 21 points
		$table->playerHand->addCard(new Card(12)); // king of hearts
		$table->playerHand->addCard(new Card(13)); // ace of clubs

		// Dealer is given no cards, since game is already over
		$table->dealerHitsUntil(22);
		self::assertEmpty($table->dealerHand->getCards());
	}

	/**
	 * @param array<int> $playerCardIDs
	 * @param array<int> $dealerCardIDs
	 */
	#[TestWith([[12, 13], [], true])] // player 21, dealer 0
	#[TestWith([[], [12, 13], true])] // player 0, dealer 21
	#[TestWith([[12, 13], [12, 13], true])] // player 21, dealer 21
	#[TestWith([[12, 12], [12, 12], false])] // player 20, dealer 20
	public function test_gameOver(array $playerCardIDs, array $dealerCardIDs, bool $expected): void {
		$table = new Table(false);
		foreach ($playerCardIDs as $cardID) {
			$table->playerHand->addCard(new Card($cardID));
		}
		foreach ($dealerCardIDs as $cardID) {
			$table->dealerHand->addCard(new Card($cardID));
		}
		self::assertSame($expected, $table->gameOver());
	}

	/**
	 * @param array<int> $playerCardIDs
	 * @param array<int> $dealerCardIDs
	 */
	#[TestWith([[12, 13], [], Result::Blackjack])] // player has blackjack
	#[TestWith([[12, 13], [12, 13], Result::Blackjack])] // both blackjack, player takes precedence
	#[TestWith([[12], [12], Result::Tie])] // equal score, no one busted
	#[TestWith([[12, 12, 12], [], Result::Lose])] // player busts, dealer does not
	#[TestWith([[], [12, 12, 12], Result::Win])] // dealer busts, player does not
	#[TestWith([[12, 12, 12], [12, 12, 12], Result::Lose])] // both bust, player takes precedence
	#[TestWith([[13], [12], Result::Win])] // player higher score, no one busted
	#[TestWith([[12], [13], Result::Lose])] // dealer higher score, no one busted
	public function test_getPlayerResult(array $playerCardIDs, array $dealerCardIDs, Result $expected): void {
		$table = new Table(false);
		foreach ($playerCardIDs as $cardID) {
			$table->playerHand->addCard(new Card($cardID));
		}
		foreach ($dealerCardIDs as $cardID) {
			$table->dealerHand->addCard(new Card($cardID));
		}
		self::assertSame($expected, $table->getPlayerResult());
	}

}
