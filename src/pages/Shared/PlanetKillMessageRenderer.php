<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Planet;

class PlanetKillMessageRenderer {

	public static function render(Planet $TargetPlanet): void {
		echo $TargetPlanet->getCombatName() ?>'s defenses are <span class="red">DESTROYED!</span><br />

		<?php
	}

}
