<?php declare(strict_types=1);

namespace Smr\Combat\Results\Kill;

use Smr\AbstractShip;
use Smr\Pages\Shared\CombatKillMessageRenderer;
use Smr\Port;

final readonly class PortDestroyedByPlayer implements KillResultInterface {

	public function __construct(public Port $target, public AbstractShip $killer) {}

	public function render(CombatKillMessageRenderer $renderer): void {
		$renderer->renderPortDestroyedByPlayer($this);
	}

}
