<?php declare(strict_types=1);

namespace Smr\Blackjack;

/**
 * Deck of Blackjack cards to be drawn from.
 */
class Deck {

	// We can have multiple decks of cards
	const NUM_DECKS = 1;
	const MAX_CARDS = 52 * self::NUM_DECKS;

	private array $cardIDs = [];

	public function __construct() {
		$this->cardIDs = range(0, self::MAX_CARDS - 1);

		// Shuffle the cards so that we can draw them randomly
		shuffle($this->cardIDs);
	}

	/**
	 * Draw a random card from this deck.
	 */
	public function drawCard(): Card {
		if (empty($this->cardIDs)) {
			throw new \Exception('No cards left to draw from this deck!');
		}
		// since the cards are already shuffled, pop off the next one
		$cardID = array_pop($this->cardIDs);
		return new Card($cardID);
	}

}
