<?php declare(strict_types=1);

namespace Smr\Blackjack;

/**
 * Classic playing card for blackjack.
 */
class Card {

	// Special card ranks
	private const int RANK_ACE = 1;
	private const int RANK_JACK = 11;
	private const int RANK_QUEEN = 12;
	private const int RANK_KING = 13;

	private const array SUITS = ['hearts', 'clubs', 'diamonds', 'spades'];

	private readonly int $rank; // non-unique rank of the card (1-indexed)

	/**
	 * Create a specific card in the deck.
	 */
	public function __construct(
		private readonly int $cardID, // unique ID in all the decks (0-indexed)
	) {
		// 52 cards per deck, 13 cards per suit
		$this->rank = ($this->cardID % 52) % 13 + 1;
	}

	public function getCardID(): int {
		return $this->cardID;
	}

	/**
	 * Return the card's blackjack value.
	 */
	public function getValue(): int {
		return match ($this->rank) {
			self::RANK_ACE => 11,
			self::RANK_JACK, self::RANK_QUEEN, self::RANK_KING => 10,
			// For normal pip (non-face) cards, value and rank are the same.
			default => $this->rank,
		};
	}

	public function isAce(): bool {
		return $this->rank === self::RANK_ACE;
	}

	public function getSuitName(): string {
		$deckID = $this->cardID % 52; //which card is this in the deck?
		$suitID = IFloor($deckID / 13);
		return self::SUITS[$suitID];
	}

	/**
	 * Returns the rank name of this card (of the 13 ranks).
	 */
	public function getRankName(): string {
		return match ($this->rank) {
			self::RANK_ACE => 'A',
			self::RANK_JACK => 'J',
			self::RANK_QUEEN => 'Q',
			self::RANK_KING => 'K',
			// For normal pip (non-face) cards, name and rank are the same.
			default => (string)$this->rank,
		};
	}

}
