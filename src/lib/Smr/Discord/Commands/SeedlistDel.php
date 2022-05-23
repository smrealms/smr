<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\DatabaseCommand;

require_once(TOOLS . 'chat_helpers/channel_msg_seedlist.php');

class SeedlistDel extends DatabaseCommand {

	public function name(): string {
		return 'del';
	}

	public function description(): string {
		return 'Delete space-delimited list of sectors (or all sectors) from the seedlist';
	}

	public function usage(): string {
		return '[sectors|all]';
	}

	public function databaseResponse(string ...$args): array {
		// delete sectors from the seedlist
		return shared_channel_msg_seedlist_del($this->player, $args);
	}

}
