<?php declare(strict_types=1);

namespace Blackjack;

/**
 * Hand of Blackjack cards.
 */
class Hand {

	private array $cards = [];
	private int $value = 0;

	/**
	 * Add a hand to this card by drawing it from $deck.
	 */
	public function drawCard(Deck $deck) : void {
		$this->cards[] = $deck->drawCard();
		$this->updateValue();
	}

	/**
	 * Return the hand's total blackjack value.
	 */
	public function getValue() : int {
		return $this->value;
	}

	/**
	 * Return the number of cards in this hand.
	 */
	public function getNumCards() : int {
		return count($this->cards);
	}

	public function getCards() : array {
		return $this->cards;
	}

	/**
	 * Does this hand have Blackjack?
	 */
	public function hasBlackjack() : bool {
		return $this->getNumCards() == 2 && $this->getValue() == 21;
	}

	/**
	 * Update the stored value of this hand.
	 */
	private function updateValue() : void {
		$numAces11 = 0; // Aces have a value of 11 by default
		$value = 0;
		foreach ($this->cards as $card) {
			if ($card->isAce()) {
				$numAces11 += 1;
			}
			$value += $card->getValue();
		}
		// Modify value of aces if we're over 21
		while ($value > 21 && $numAces11 > 0) {
			$value -= 10;
			$numAces11 -= 1;
		}
		$this->value = $value;
	}

}
