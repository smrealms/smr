<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Combat\Results\ForceFullCombatResults;
use Smr\Template;

class ForceFullCombatResultsRenderer {

	public static function render(
		Template $template,
		ForceFullCombatResults $FullForceCombatResults,
	): void {
		if ($FullForceCombatResults->bump) { ?>
			<h1>Force Results</h1><br />
			<?php ForcesCombatResultsRenderer::render(
				template: $template,
				ForcesCombatResults: $FullForceCombatResults->forces,
			);
		} else { ?>
			<h1>Attacker Results</h1><br />
			<?php ForceTraderTeamCombatResultsRenderer::render(template: $template, TraderTeamCombatResults: $FullForceCombatResults->attackers);
		} ?>
		<br />
		<br />
		<img src="images/creonti_cruiser.jpg" alt="Creonti Cruiser" title="Creonti Cruiser"><br />
		<br />
		<?php if (!$FullForceCombatResults->bump) { ?>
			<h1>Force Results</h1><br />
			<?php ForcesCombatResultsRenderer::render(
				template: $template,
				ForcesCombatResults: $FullForceCombatResults->forces,
			);
		} else { ?>
			<h1>Defender Results</h1><br />
			<?php ForceTraderTeamCombatResultsRenderer::render(template: $template, TraderTeamCombatResults: $FullForceCombatResults->attackers);
		}

	}

}
