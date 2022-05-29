<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\DatabaseCommand;

require_once(TOOLS . 'chat_helpers/channel_msg_forces.php');

class Forces extends DatabaseCommand {

	public function name(): string {
		return 'forces';
	}

	public function description(): string {
		return 'Print time until next expiring force. Arguments optional.';
	}

	public function usage(): string {
		return '[galaxy name | seedlist]';
	}

	public function databaseResponse(string ...$args): array {
		// print the next expiring forces
		return shared_channel_msg_forces($this->player, ...$args);
	}

}
