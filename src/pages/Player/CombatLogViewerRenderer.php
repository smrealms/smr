<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\Combat\Results\ForceFullCombatResults;
use Smr\Combat\Results\FullCombatResults;
use Smr\Combat\Results\PlanetFullCombatResults;
use Smr\Combat\Results\PortFullCombatResults;
use Smr\Combat\Results\TraderFullCombatResults;
use Smr\Pages\Shared\ForceFullCombatResultsRenderer;
use Smr\Pages\Shared\PlanetFullCombatResultsRenderer;
use Smr\Pages\Shared\PortFullCombatResultsRenderer;
use Smr\Pages\Shared\TraderFullCombatResultsRenderer;
use Smr\Player;
use Smr\Template;

class CombatLogViewerRenderer {

	public static function render(
		Template $template,
		int $CombatLogSector,
		string $CombatLogTimestamp,
		FullCombatResults $CombatResults,
		?string $PreviousLogHREF,
		?string $NextLogHREF,
		Player $ThisPlayer,
	): void {
		if (isset($PreviousLogHREF) || isset($NextLogHREF)) { ?>
			<div class="center"><?php
			if (isset($PreviousLogHREF)) {
				?><a href="<?php echo $PreviousLogHREF ?>"><img title="Previous" alt="Previous" src="images/album/rew.jpg" /></a><?php
			}
			if (isset($NextLogHREF)) {
				?><a href="<?php echo $NextLogHREF ?>"><img title="Next" alt="Next" src="images/album/fwd.jpg" /></a><?php
			} ?>
			</div><?php
		} ?>
		Sector <?php echo $CombatLogSector ?><br />
		<?php echo $CombatLogTimestamp ?><br />
		<br />

		<?php
		if ($CombatResults instanceof TraderFullCombatResults) {
			TraderFullCombatResultsRenderer::render(
				template: $template,
				TraderCombatResults: $CombatResults,
				MinimalDisplay: false,
				AttackLogLink: null,
				ThisPlayer: $ThisPlayer,
			);
		} elseif ($CombatResults instanceof ForceFullCombatResults) {
			ForceFullCombatResultsRenderer::render(
				template: $template,
				FullForceCombatResults: $CombatResults,
			);
		} elseif ($CombatResults instanceof PortFullCombatResults) {
			PortFullCombatResultsRenderer::render(
				template: $template,
				FullPortCombatResults: $CombatResults,
				MinimalDisplay: false,
				AlreadyDestroyed: false,
				ThisPlayer: $ThisPlayer,
				AttackLogLink: null,
			);
		} elseif ($CombatResults instanceof PlanetFullCombatResults) {
			PlanetFullCombatResultsRenderer::render(
				template: $template,
				FullPlanetCombatResults: $CombatResults,
				MinimalDisplay: false,
				ThisPlayer: $ThisPlayer,
				AttackLogLink: null,
			);
		} else {
			throw new Exception('Unknown combat result type');
		}

	}

}
