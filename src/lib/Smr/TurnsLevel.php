<?php declare(strict_types=1);

namespace Smr;

/**
 * Enumerates the warning levels that correspond to player turn thresholds
 */
enum TurnsLevel {

	case None;
	case Low;
	case Medium;
	case High;

	/**
	 * Returns the CSS class color to use when displaying the player's turns
	 */
	public function color(): string {
		return match ($this) {
			self::None, self::Low => 'red',
			self::Medium => 'yellow',
			self::High => 'green',
		};
	}

	/**
	 * Returns the warning to display when player's turns get low.
	 */
	public function message(): string {
		return match ($this) {
			self::None => '<span class="red">WARNING</span>: You have run out of turns!',
			self::Low => '<span class="red">WARNING</span>: You are almost out of turns!',
			self::Medium => '<span class="yellow">WARNING</span>: You are running out of turns!',
			self::High => '',
		};
	}

}
