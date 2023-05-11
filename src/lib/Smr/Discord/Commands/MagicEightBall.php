<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\Command;

require_once(TOOLS . 'chat_helpers/channel_msg_8ball.php');

class MagicEightBall extends Command {

	public function name(): string {
		return '8ball';
	}

	public function description(): string {
		return 'Ask a question, get a magic 8-ball answer.';
	}

	public function usage(): string {
		return '[question]';
	}

	public function response(string ...$args): array {
		if (count($args) === 0) {
			return ['Do you have a question for the magic 8-ball?'];
		}
		return [shared_channel_msg_8ball()];
	}

}
