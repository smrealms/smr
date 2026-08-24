<?php declare(strict_types=1);

namespace Smr\Combat\Results\Kill;

use Smr\AbstractShip;
use Smr\Pages\Shared\CombatKillMessageRenderer;
use Smr\Planet;

final readonly class PlanetDestroyedByPlayer implements KillResultInterface {

	public function __construct(public Planet $target, public AbstractShip $killer) {}

	public function render(CombatKillMessageRenderer $renderer): void {
		$renderer->renderPlanetDestroyedByPlayer($this);
	}

}
