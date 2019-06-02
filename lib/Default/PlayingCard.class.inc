<?php

/**
 * Classic playing card for blackjack.
 */
class PlayingCard {
	//num of decks and cards
	const NUM_DECKS = 1;
	const MAX_CARDS = 52 * self::NUM_DECKS;

	// Special card rank IDs
	private const RANK_ACE = 1;
	private const RANK_JACK = 11;
	private const RANK_QUEEN = 12;
	private const RANK_KING = 13;

	// Mapping between rank IDs and display/suit name
	private const RANK_NAMES = [
		self::RANK_ACE => 'A',
		self::RANK_JACK => 'J',
		self::RANK_QUEEN => 'Q',
		self::RANK_KING => 'K',
	];
	private const SUITS = ['hearts', 'clubs', 'diamonds', 'spades'];

	private $cardID; // unique ID in the deck (0-indexed)
	private $rankID; // non-unique rank ID of the card (1-indexed)

	/**
	 * Create a specific or random card in the deck.
	 */
	public function __construct($cardID = null) {
		if (is_null($cardID)) {
			$this->cardID = rand(0, self::MAX_CARDS - 1);
		} else {
			$this->cardID = $cardID;
		}
		// 52 cards per deck, 13 cards per suit
		$this->rankID = ($this->cardID % 52) % 13 + 1;
	}

	/**
	 * Return the card's blackjack value.
	 */
	public function getValue() {
		if ($this->rankID == self::RANK_JACK ||
		    $this->rankID == self::RANK_QUEEN ||
		    $this->rankID == self::RANK_KING) {
			return 10;
		} elseif ($this->isAce()) {
			return 11;
		} else {
			// For normal pip (non-face) cards, value and name are the same.
			return $this->rankID;
		}
	}

	public function isAce() {
		return $this->rankID == self::RANK_ACE;
	}

	public function getSuitName() {
		$deckID = $this->cardID % 52; //which card is this in the deck?
		$suitID = floor($deckID / 13);
		return self::SUITS[$suitID];
	}

	/**
	 * Returns the rank name of this card (of the 13 ranks).
	 */
	public function getRankName() {
		if (isset(self::RANK_NAMES[$this->rankID])) {
			return self::RANK_NAMES[$this->rankID];
		} else {
			// For normal pip (non-face) cards, value and name are the same.
			return $this->rankID;
		}
	}
}
