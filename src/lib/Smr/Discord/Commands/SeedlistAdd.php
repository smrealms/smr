<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\DatabaseCommand;

require_once(TOOLS . 'chat_helpers/channel_msg_seedlist.php');

class SeedlistAdd extends DatabaseCommand {

	public function name(): string {
		return 'add';
	}

	public function description(): string {
		return 'Add space-delimited list of sectors to the seedlist';
	}

	public function usage(): string {
		return '[sectors]';
	}

	public function databaseResponse(string ...$args): array {
		// add sectors to the seedlist
		return shared_channel_msg_seedlist_add($this->player, $args);
	}

}
