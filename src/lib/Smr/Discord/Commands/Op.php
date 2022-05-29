<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\DatabaseCommand;

require_once(TOOLS . 'chat_helpers/channel_msg_op_info.php');

class Op extends DatabaseCommand {

	public function name(): string {
		return 'op';
	}

	public function description(): string {
		return 'Get information about the next scheduled op';
	}

	public function databaseResponse(string ...$args): array {
		// print info about the next op
		return shared_channel_msg_op_info($this->player);
	}

}
