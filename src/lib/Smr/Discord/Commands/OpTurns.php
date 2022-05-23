<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\DatabaseCommand;

require_once(TOOLS . 'chat_helpers/channel_msg_op_turns.php');

class OpTurns extends DatabaseCommand {

	public function name(): string {
		return 'turns';
	}

	public function description(): string {
		return 'Get the turns of op attendees';
	}

	public function databaseResponse(string ...$args): array {
		// print list of attendees and their turns
		return shared_channel_msg_op_turns($this->player);
	}

}
