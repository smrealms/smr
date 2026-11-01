<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Combat\Results\Full\ForceFullCombatResults;

class ForceFullCombatResultsRenderer {

	public static function render(ForceFullCombatResults $FullForceCombatResults): void {
		if ($FullForceCombatResults->bump) { ?>
			<h1>Force Results</h1><br />
			<?php ForcesCombatResultsRenderer::render($FullForceCombatResults->forces);
		} else { ?>
			<h1>Attacker Results</h1><br />
			<?php ForceTraderTeamCombatResultsRenderer::render($FullForceCombatResults->attackers);
		} ?>
		<br />
		<br />
		<img src="images/creonti_cruiser.jpg" alt="Creonti Cruiser" title="Creonti Cruiser"><br />
		<br />
		<?php if (!$FullForceCombatResults->bump) { ?>
			<h1>Force Results</h1><br />
			<?php ForcesCombatResultsRenderer::render($FullForceCombatResults->forces);
		} else { ?>
			<h1>Defender Results</h1><br />
			<?php ForceTraderTeamCombatResultsRenderer::render($FullForceCombatResults->attackers);
		}

	}

}
