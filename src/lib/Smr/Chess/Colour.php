<?php declare(strict_types=1);

namespace Smr\Chess;

/**
 * Enumeration of possible player colours in a chess game.
 */
enum Colour: string {

	// Backing values map to database values and must not be changed.
	case White = 'White';
	case Black = 'Black';

	/**
	 * Return the opposite colour
	 */
	public function opposite(): self {
		return match ($this) {
			self::White => self::Black,
			self::Black => self::White,
		};
	}

}
