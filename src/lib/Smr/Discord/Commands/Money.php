<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\DatabaseCommand;

require_once(TOOLS . 'chat_helpers/channel_msg_money.php');

class Money extends DatabaseCommand {

	public function name(): string {
		return 'money';
	}

	public function description(): string {
		return 'Get alliance financial status';
	}

	public function databaseResponse(string ...$args): array {
		return shared_channel_msg_money($this->player);
	}

}
