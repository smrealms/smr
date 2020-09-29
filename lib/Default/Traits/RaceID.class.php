<?php declare(strict_types=1);

namespace Traits;

/**
 * Implements the interface for classes that need a $raceID property.
 */
trait RaceID {
	protected int $raceID;

	public function getRaceID() : int {
		return $this->raceID;
	}

	public function getRaceName() : string {
		return \Globals::getRaceName($this->raceID);
	}
}
