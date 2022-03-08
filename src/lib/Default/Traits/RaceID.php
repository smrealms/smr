<?php declare(strict_types=1);

namespace Traits;

use Smr\Race;

/**
 * Implements the interface for classes that need a $raceID property.
 */
trait RaceID {

	protected int $raceID;

	public function getRaceID(): int {
		return $this->raceID;
	}

	public function getRaceName(): string {
		return Race::getName($this->raceID);
	}

}
