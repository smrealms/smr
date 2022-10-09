<?php declare(strict_types=1);

namespace Smr\Irc;

use Closure;

/**
 * Manage callback events in the IRC driver.
 *
 * Some of the IRC listeners have to make queries to the IRC server before
 * continuing with processing, and this class stores the callback for those
 * queries so we can proceed once the server responds.
 */
class CallbackEvent {

	/** @var array<self> */
	private static array $EVENTS;

	/**
	 * @return array<self>
	 */
	public static function getAll(): array {
		return self::$EVENTS;
	}

	public static function add(self $eventToAdd): void {
		self::$EVENTS[] = $eventToAdd;
	}

	public static function remove(self $eventToRemove): void {
		foreach (self::$EVENTS as $key => $event) {
			if ($event === $eventToRemove) {
				unset(self::$EVENTS[$key]);
				break;
			}
		}
	}

	public function __construct(
		public readonly string $type,
		public readonly string $channel,
		public readonly string $nick,
		public readonly Closure $callback,
		public readonly int $time,
		public readonly bool $validate
	) {}

}
