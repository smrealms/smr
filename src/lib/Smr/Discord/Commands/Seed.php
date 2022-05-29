<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\DatabaseCommand;

require_once(TOOLS . 'chat_helpers/channel_msg_seed.php');

class Seed extends DatabaseCommand {

	public function name(): string {
		return 'seed';
	}

	public function description(): string {
		return 'List sectors with missing seeds';
	}

	public function databaseResponse(string ...$args): array {
		return shared_channel_msg_seed($this->player);
	}

}
