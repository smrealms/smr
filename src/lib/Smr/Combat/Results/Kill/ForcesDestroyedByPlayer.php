<?php declare(strict_types=1);

namespace Smr\Combat\Results\Kill;

use Smr\AbstractShip;
use Smr\Force;
use Smr\Pages\Shared\CombatKillMessageRenderer;

final readonly class ForcesDestroyedByPlayer implements KillResultInterface {

	public function __construct(public Force $target, public AbstractShip $killer) {}

	public function render(CombatKillMessageRenderer $renderer): void {
		$renderer->renderForcesDestroyedByPlayer($this);
	}

}
