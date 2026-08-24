<?php declare(strict_types=1);

namespace Smr\Combat\Results\Kill;

use Smr\Pages\Shared\CombatKillMessageRenderer;
use Smr\Planet;
use Smr\Ship;

final readonly class PlanetDestroyedByPlayer implements KillResultInterface {

	public function __construct(public Planet $target, public Ship $killer) {}

	public function render(CombatKillMessageRenderer $renderer): void {
		$renderer->renderPlanetDestroyedByPlayer($this);
	}

}
