<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\DatabaseCommand;

require_once(TOOLS . 'chat_helpers/channel_msg_op_list.php');

class OpList extends DatabaseCommand {

	public function name(): string {
		return 'list';
	}

	public function description(): string {
		return 'Get the op attendee list';
	}

	public function databaseResponse(string ...$args): array {
		// print list of attendees
		return shared_channel_msg_op_list($this->player);
	}

}
