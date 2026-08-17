<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Alliance;
use Smr\Pages\Shared\PlanetListRenderer;
use Smr\Planet;
use Smr\Player;
use Smr\Template;

class ListPlanetFinancialRenderer {

/** @param array<\Smr\Planet> $AllPlanets */
public static function render(Template $template, bool $CanViewBonds, ?Alliance $Alliance, array $AllPlanets, ?Planet $PlayerPlanet, Player $ThisPlayer): void {
if (!$CanViewBonds) { ?>
	<div class="center">
		You do not have permission to view planet financials!
	</div><?php
} else {
	PlanetListRenderer::render(template: $template, Alliance: $Alliance, AllPlanets: $AllPlanets, PlayerPlanet: $PlayerPlanet, ThisPlayer: $ThisPlayer, Financial: true);
}

}

}
