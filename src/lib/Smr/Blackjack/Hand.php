<?php declare(strict_types=1);

namespace Smr\Blackjack;

/**
 * Hand of Blackjack cards.
 */
class Hand {

	private array $cards = [];
	private int $value = 0;

	/**
	 * Return the hand's total blackjack value.
	 */
	public function getValue(): int {
		return $this->value;
	}

	/**
	 * Return the number of cards in this hand.
	 */
	public function getNumCards(): int {
		return count($this->cards);
	}

	public function getCards(): array {
		return $this->cards;
	}

	/**
	 * Does this hand have Blackjack?
	 */
	public function hasBlackjack(): bool {
		return $this->getNumCards() == 2 && $this->getValue() == 21;
	}

	/**
	 * Has this hand busted?
	 */
	public function hasBusted(): bool {
		return $this->getValue() > 21;
	}

	/**
	 * Add a card to this hand.
	 */
	public function addCard(Card $card): void {
		// add the card to the hand
		$this->cards[] = $card;

		// update the total value of the hand
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
