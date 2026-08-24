<?php declare(strict_types=1);

namespace Smr\Combat\Results\Kill;

use Smr\Force;
use Smr\Pages\Shared\CombatKillMessageRenderer;
use Smr\Planet;
use Smr\Port;
use Smr\Ship;

final readonly class PlayerKilledByEnvironment implements KillResultInterface {

	public function __construct(
		public Ship $target,
		public Port|Planet|Force $killer,
		public int $deadExp,
		public int $lostCredits,
	) {}

	public function render(CombatKillMessageRenderer $renderer): void {
		$renderer->renderPlayerKilledByEnvironment($this);
	}

}
