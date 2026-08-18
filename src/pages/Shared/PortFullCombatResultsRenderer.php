<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Combat\Results\PortFullCombatResults;
use Smr\Player;
use Smr\Template;

class PortFullCombatResultsRenderer {

	public static function render(
		Template $template,
		bool $MinimalDisplay,
		bool $AlreadyDestroyed,
		?PortFullCombatResults $FullPortCombatResults,
		Player $ThisPlayer,
		?string $AttackLogLink,
	): void {
		if ($FullPortCombatResults !== null) {
			if (!$MinimalDisplay) { ?>
				<h1>Attacker Results</h1><br /><?php
			}
			PortTraderTeamCombatResultsRenderer::render(
				template: $template,
				TraderTeamCombatResults: $FullPortCombatResults->attackers,
				MinimalDisplay: $MinimalDisplay,
				ThisPlayer: $ThisPlayer,
			);
		} elseif ($AlreadyDestroyed && !$MinimalDisplay) {
			?><span class="bold">The port is already destroyed.</span><?php
		}
		?><br /><?php
		if (!$MinimalDisplay) { ?>
			<br />
			<img src="images/portAttack.jpg" width="480" height="330" alt="Port Attack" title="Port Attack"><br /><?php
		}
		if ($FullPortCombatResults !== null) {
			if (!$MinimalDisplay) { ?>
				<br />
				<h1>Port Results</h1><br /><?php
			}
			PortCombatResultsRenderer::render(
				template: $template,
				PortCombatResults: $FullPortCombatResults->port,
				MinimalDisplay: $MinimalDisplay,
				ThisPlayer: $ThisPlayer,
				AttackLogLink: $AttackLogLink,
			);
		}

	}

}
