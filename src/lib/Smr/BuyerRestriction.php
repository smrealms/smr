<?php declare(strict_types=1);

namespace Smr;

/**
 * Enumeration of buyer restrictions for ships and weapons.
 */
enum BuyerRestriction: int {

	// Backing values must not be changed, since they map to database values.
	case None = 0;
	case Good = 1;
	case Evil = 2;
	case Newbie = 3;
	case Port = 4;
	case Planet = 5;

	/**
	 * Does the player pass the restriction?
	 */
	public function passes(AbstractPlayer $player): bool {
		return match ($this) {
			self::None => true, // no restriction, all players pass
			self::Good => $player->hasGoodAlignment(),
			self::Evil => $player->hasEvilAlignment(),
			self::Newbie => $player->hasNewbieStatus(),
			self::Port => false, // player is not a port
			self::Planet => false, // player is not a planet
		};
	}

	public function display(): string {
		return match ($this) {
			self::None => '', // no display
			self::Good => '<div class="dgreen">Good</div>',
			self::Evil => '<div class="red">Evil</div>',
			self::Newbie => '<div style="color: #06F;">Newbie</div>',
			self::Port => '<div class="yellow">Port</div>',
			self::Planet => '<div class="yellow">Planet</div>',
		};
	}

}
