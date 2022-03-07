<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Container\DiContainer;

/**
 * Stores the current time as a fixed value.
 * This is useful for ensuring that times are used consistently within the
 * context of an individual page request. It does not, however, represent
 * the start time of the request.
 *
 * The static methods are a convenience wrapper around the instance of this
 * class stored in the DI container (which is set on the first query, and
 * then remains unchanged in subsequent calls).
 */
class Epoch {

	private float $microtime;
	private int $time;

	public function __construct() {
		$this->microtime = microtime(true);
		$this->time = IFloor($this->microtime);
	}

	public function getMicrotime(): float {
		return $this->microtime;
	}

	public function getTime(): int {
		return $this->time;
	}

	/**
	 * Returns the instance of this class from the DI container.
	 * The first time this is called, it will populate the DI container,
	 * and this will be the time associated with the page request.
	 */
	private static function getInstance(): self {
		return DiContainer::get(self::class);
	}

	/**
	 * Return the time (in seconds, with microsecond-level precision)
	 * associated with a page request (i.e. stored in the DI container).
	 */
	public static function microtime(): float {
		return self::getInstance()->getMicrotime();
	}

	/**
	 * Return the time (in seconds) associated with a page request
	 * (i.e. stored in the DI container).
	 */
	public static function time(): int {
		return self::getInstance()->getTime();
	}

	/**
	 * Update the time associated with this page request
	 * (i.e. stored in the DI container).
	 *
	 * NOTE: This should never be called by normal page requests, and should
	 * only be used by the CLI programs that run continuously.
	 */
	public static function update(): void {
		if (!defined('NPC_SCRIPT')) {
			throw new Exception('Only call this function from CLI programs!');
		}
		DiContainer::getContainer()->set(self::class, new self());
	}

}
