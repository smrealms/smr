<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\DatabaseCommand;

require_once(TOOLS . 'chat_helpers/channel_msg_seedlist.php');

class Seedlist extends DatabaseCommand {

	public function name(): string {
		return 'seedlist';
	}

	public function description(): string {
		return 'Print the entire seedlist';
	}

	public function databaseResponse(string ...$args): array {
		// print the entire seedlist
		return shared_channel_msg_seedlist($this->player);
	}

}
