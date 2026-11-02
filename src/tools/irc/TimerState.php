<?php declare(strict_types=1);

namespace Smr\Irc;

final class TimerState {

	/** @var list<TimerEvent> */
	private array $events = [];

	public function add(TimerEvent $event): void {
		$this->events[] = $event;
	}

	/**
	 * @return list<TimerEvent>
	 */
	public function eventsForChannel(string $channel): array {
		return array_values(array_filter(
			$this->events,
			fn($event) => $event->channel === $channel,
		));
	}

	/**
	 * Collect and return expired events, removing them from the tracked state.
	 *
	 * @return list<TimerEvent>
	 */
	public function takeExpired(int $now): array {
		$expiredEvents = [];
		$pendingEvents = [];
		foreach ($this->events as $event) {
			if ($event->expiresAt < $now) {
				$expiredEvents[] = $event;
			} else {
				$pendingEvents[] = $event;
			}
		}
		$this->events = $pendingEvents;
		return $expiredEvents;
	}

}
