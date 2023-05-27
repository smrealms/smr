<?php declare(strict_types=1);

namespace Smr\Blackjack;

/**
 * A game of blackjack between the dealer and a player.
 */
class Table {

	private Deck $deck;
	public Hand $playerHand;
	public Hand $dealerHand;

	public function __construct(bool $deal = true) {
		$this->deck = new Deck();
		$this->playerHand = new Hand();
		$this->dealerHand = new Hand();

		if ($deal) {
			$this->deal();
		}
	}

	/**
	 * Deal the initial 4 cards (2 for player, 2 for dealer)
	 */
	public function deal(): void {
		$this->playerHand->addCard($this->deck->drawCard());
		$this->dealerHand->addCard($this->deck->drawCard());
		$this->playerHand->addCard($this->deck->drawCard());
		$this->dealerHand->addCard($this->deck->drawCard());
	}

	/**
	 * Player draws a card
	 */
	public function playerHits(): void {
		$this->playerHand->addCard($this->deck->drawCard());
	}

	/**
	 * Dealer draws cards until their hand has a value >= $limit
	 */
	public function dealerHitsUntil(int $limit): void {
		if ($this->playerHand->getValue() < 21) {
			while ($this->dealerHand->getValue() < $limit) {
				$this->dealerHand->addCard($this->deck->drawCard());
			}
		}
	}

	/**
	 * Check if the game is forcibly over (a hand has blackjack or busted)
	 */
	public function gameOver(): bool {
		return $this->playerHand->getValue() >= 21 || $this->dealerHand->getValue() >= 21;
	}

	/**
	 * Get the result of the completed game from the player's perspective
	 */
	public function getPlayerResult(): Result {
		return match (true) {
			$this->playerHand->hasBusted() => Result::Lose,
			$this->playerHand->hasBlackjack() => Result::Blackjack,
			$this->playerHand->getValue() === $this->dealerHand->getValue() => Result::Tie,
			$this->playerHand->getValue() > $this->dealerHand->getValue() => Result::Win,
			$this->dealerHand->hasBusted() => Result::Win,
			default => Result::Lose,
		};
	}

}
