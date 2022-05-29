<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Smr\Discord\DatabaseCommand;
use SmrPlayer;

class Turns extends DatabaseCommand {

	public function name(): string {
		return 'turns';
	}

	public function description(): string {
		return 'Show current turns';
	}

	public function databaseResponse(string ...$args): array {
		return array_map([$this, 'getTurnsMessage'], $this->player->getSharingPlayers(true));
	}

	private function getTurnsMessage(SmrPlayer $player): string {
		// turns only update when the player is active, so calculate current turns
		$turns = min(
			$player->getTurns() + $player->getTurnsGained(time(), true),
			$player->getMaxTurns()
		);
		$msg = $player->getPlayerName() . " has $turns/" . $player->getMaxTurns() . ' turns.';

		// Calculate time to max turns if under the max
		$timeToMax = $player->getTimeUntilMaxTurns(time(), true);
		if ($timeToMax > 0) {
			$msg .= ' At max turns in ' . format_time($timeToMax, true) . '.';
		}

		return $msg;
	}

}
