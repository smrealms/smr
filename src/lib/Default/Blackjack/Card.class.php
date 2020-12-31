<?php declare(strict_types=1);

namespace Blackjack;

/**
 * Classic playing card for blackjack.
 */
class Card {

	// Special card ranks
	private const RANK_ACE = 1;
	private const RANK_JACK = 11;
	private const RANK_QUEEN = 12;
	private const RANK_KING = 13;

	// Mapping between ranks and display/suit name
	private const RANK_NAMES = [
		self::RANK_ACE => 'A',
		self::RANK_JACK => 'J',
		self::RANK_QUEEN => 'Q',
		self::RANK_KING => 'K',
	];
	private const SUITS = ['hearts', 'clubs', 'diamonds', 'spades'];

	private int $cardID; // unique ID in all the decks (0-indexed)
	private int $rank; // non-unique rank of the card (1-indexed)

	/**
	 * Create a specific card in the deck.
	 */
	public function __construct(int $cardID) {
		$this->cardID = $cardID;
		// 52 cards per deck, 13 cards per suit
		$this->rank = ($this->cardID % 52) % 13 + 1;
	}

	public function getCardID() : int {
		return $this->cardID;
	}

	/**
	 * Return the card's blackjack value.
	 */
	public function getValue() : int {
		if ($this->rank == self::RANK_JACK ||
		    $this->rank == self::RANK_QUEEN ||
		    $this->rank == self::RANK_KING) {
			return 10;
		} elseif ($this->isAce()) {
			return 11;
		} else {
			// For normal pip (non-face) cards, value and rank are the same.
			return $this->rank;
		}
	}

	public function isAce() : bool {
		return $this->rank == self::RANK_ACE;
	}

	public function getSuitName() : string {
		$deckID = $this->cardID % 52; //which card is this in the deck?
		$suitID = floor($deckID / 13);
		return self::SUITS[$suitID];
	}

	/**
	 * Returns the rank name of this card (of the 13 ranks).
	 */
	public function getRankName() : string {
		if (isset(self::RANK_NAMES[$this->rank])) {
			return self::RANK_NAMES[$this->rank];
		} else {
			// For normal pip (non-face) cards, name and rank are the same.
			return (string)$this->rank;
		}
	}
}
