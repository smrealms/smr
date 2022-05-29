<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\DatabaseCommand;

class Game extends DatabaseCommand {

	public function name(): string {
		return 'game';
	}

	public function description(): string {
		return 'Get name of game linked to this channel';
	}

	public function databaseResponse(string ...$args): array {
		$gameName = $this->player->getGame(true)->getDisplayName();
		return ['I am linked to game `' . $gameName . '` in this channel.'];
	}

}
