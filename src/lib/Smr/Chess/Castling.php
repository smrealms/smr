<?php declare(strict_types=1);

namespace Smr\Chess;

/**
 * Enum values are database types and must not be changed.
 */
enum Castling: string {

	case Kingside = 'King';
	case Queenside = 'Queen';

}
