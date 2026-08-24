<?php declare(strict_types=1);

namespace Smr\Combat\Results\Kill;

use Smr\AbstractShip;
use Smr\Pages\Shared\CombatKillMessageRenderer;

final readonly class PlayerKilledByPlayer implements KillResultInterface {

	public function __construct(
		public AbstractShip $target,
		public AbstractShip $killer,
		public int $deadExp,
		public int $killerExp,
		public int $killerCredits,
	) {}

	public function render(CombatKillMessageRenderer $renderer): void {
		$renderer->renderPlayerKilledByPlayer($this);
	}

}
