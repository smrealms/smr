<?php declare(strict_types=1);

namespace Smr\Irc;

final readonly class TimerEvent {

	public function __construct(
		public int $expiresAt,
		public string $message,
		public string $channel,
	) {}

}
