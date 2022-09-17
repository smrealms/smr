<?php declare(strict_types=1);

namespace Smr\Blackjack;

/**
 * Defines all the possible results of a Blackjack game
 * from the perspective of the player.
 */
enum Result {

	case Win;
	case Blackjack;
	case Tie;
	case Lose;

}
