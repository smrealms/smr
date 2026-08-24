<?php declare(strict_types=1);

namespace Smr\Combat\Results\Kill;

use Smr\Pages\Shared\CombatKillMessageRenderer;
use Smr\Port;
use Smr\Ship;

final readonly class PortDestroyedByPlayer implements KillResultInterface {

	public function __construct(public Port $target, public Ship $killer) {}

	public function render(CombatKillMessageRenderer $renderer): void {
		$renderer->renderPortDestroyedByPlayer($this);
	}

}
