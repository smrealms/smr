<?php declare(strict_types=1);

namespace Smr\Combat\Results\Kill;

use Smr\Pages\Shared\CombatKillMessageRenderer;
use Smr\Ship;

final readonly class PlayerKilledByPlayer implements KillResultInterface {

	public function __construct(
		public Ship $target,
		public Ship $killer,
		public int $deadExp,
		public int $killerExp,
		public int $killerCredits,
	) {}

	public function render(CombatKillMessageRenderer $renderer): void {
		$renderer->renderPlayerKilledByPlayer($this);
	}

}
