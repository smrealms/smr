<?php declare(strict_types=1);

/**
 * Stores the current time.
 */
class Time {

	private float $microtime;
	private int $time;

	public function __construct() {
		$this->microtime = microtime(true);
		$this->time = IFloor($this->microtime);
	}

	public function getMicroTime() : float {
		return $this->microtime;
	}

	public function getTime() : int {
		return $this->time;
	}

}
