<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Combat\Results\Full\PlanetFullCombatResults;
use Smr\Player;
use Smr\Template;

class PlanetFullCombatResultsRenderer {

	public static function render(
		Template $template,
		bool $MinimalDisplay,
		PlanetFullCombatResults $FullPlanetCombatResults,
		Player $ThisPlayer,
		?string $AttackLogLink,
	): void {
		if (!$MinimalDisplay) { ?>
			<h1>Attacker Results</h1><br /><?php
		}
		PlanetTraderTeamCombatResultsRenderer::render(
			template: $template,
			TraderTeamCombatResults: $FullPlanetCombatResults->attackers,
			MinimalDisplay: $MinimalDisplay,
			ThisPlayer: $ThisPlayer,
		);
		?><br /><?php
		if (!$MinimalDisplay) { ?>
			<br />
			<img src="images/planetAttack.jpg" width="480" height="330" alt="Planet Attack" title="Planet Attack"><br />
			<br />
			<h1>Planet Results</h1><br /><?php
		}
		PlanetCombatResultsRenderer::render(
			template: $template,
			PlanetCombatResults: $FullPlanetCombatResults->planet,
			MinimalDisplay: $MinimalDisplay,
			ThisPlayer: $ThisPlayer,
			AttackLogLink: $AttackLogLink,
		);

	}

}
