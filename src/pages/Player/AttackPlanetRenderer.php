<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\Full\PlanetFullCombatResults;
use Smr\Globals;
use Smr\Pages\Shared\PlanetFullCombatResultsRenderer;
use Smr\Planet;
use Smr\Player;
use Smr\Template;

class AttackPlanetRenderer {

	public static function render(
		Template $template,
		PlanetFullCombatResults $FullPlanetCombatResults,
		bool $OverrideDeath,
		Planet $Planet,
		Player $ThisPlayer,
	): void {
		PlanetFullCombatResultsRenderer::render(
			template: $template,
			MinimalDisplay: false,
			FullPlanetCombatResults: $FullPlanetCombatResults,
			ThisPlayer: $ThisPlayer,
			AttackLogLink: null,
		); ?><br />
		<br />
		<div class="center"><?php
		if (!$OverrideDeath && !$Planet->isBusted()) { ?>
			<div class="buttonA">
				<a href="<?php echo $Planet->getAttackHREF() ?>" class="buttonA">Continue Attack</a>
			</div><?php
		} else {
			if ($OverrideDeath) {
				?><span class="red">You have been destroyed.</span><?php
			} else {
				?><span class="yellow">You have breached the planetary defenses.</span><?php
			} ?>
			<br /><br />
			<div class="buttonA"><?php
				if ($OverrideDeath) { ?>
					<a href="<?php echo Globals::getCurrentSectorHREF() ?>" class="buttonA">Let there be pod</a><?php
				} else { ?>
					<a href="<?php echo Globals::getCurrentSectorHREF() ?>" class="buttonA">Current Sector</a><?php
					if ($Planet->exists()) { ?>
						<a href="<?php echo $Planet->getLandHREF(); ?>" class="buttonA">Land on Planet (<?php echo TURNS_TO_LAND; ?>)</a><?php
					}
				} ?>
			</div><?php
		} ?>
		</div>

		<?php
	}

}
