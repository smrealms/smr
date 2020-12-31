<?php declare(strict_types=1);

namespace Blackjack;

/**
 * Deck of Blackjack cards to be drawn from.
 */
class Deck {

	// We can have multiple decks of cards
	const NUM_DECKS = 1;
	const MAX_CARDS = 52 * self::NUM_DECKS;

	private array $drawnCardIDs = [];

	/**
	 * Draw a random card from this deck.
	 */
	public function drawCard() : Card {
		if (count($this->drawnCardIDs) === self::MAX_CARDS) {
			throw new \Exception('No cards left to draw from this deck!');
		}
		while ($cardID = rand(0, self::MAX_CARDS - 1)) {
			if (!in_array($cardID, $this->drawnCardIDs)) {
				break;
			}
		}
		$this->drawnCardIDs[] = $cardID;
		return new Card($cardID);
	}

}
