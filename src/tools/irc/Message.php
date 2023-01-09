<?php declare(strict_types=1);

namespace Smr\Irc;

/**
 * Data storage class for components of an IRC message.
 * Intended for use with PRIVMSG message types.
 */
class Message {

	public function __construct(
		public readonly string $nick,
		public readonly string $user,
		public readonly string $host,
		public readonly string $channel,
		public readonly string $text,
	) {}

}
