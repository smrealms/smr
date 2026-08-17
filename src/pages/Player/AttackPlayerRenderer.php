<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\TraderFullCombatResults;
use Smr\Globals;
use Smr\Pages\Shared\TraderFullCombatResultsRenderer;
use Smr\Player;
use Smr\Template;

class AttackPlayerRenderer {

	public static function render(Template $template, TraderFullCombatResults $TraderCombatResults, ?Player $Target, bool $OverrideDeath, Player $ThisPlayer): void {
		TraderFullCombatResultsRenderer::render(
			template: $template,
			MinimalDisplay: false,
			TraderCombatResults: $TraderCombatResults,
			AttackLogLink: null,
			ThisPlayer: $ThisPlayer,
		); ?><br />
		<br />
		<div class="center"><?php
			if ($Target !== null) { ?>
				<div class="buttonA">
					<a href="<?php echo $Target->getAttackTraderHREF(); ?>" class="buttonA">Continue Attack</a>
				</div><?php
			} else { ?>
				<h2>The battle has ended!</h2><br />
				<div class="buttonA"><?php
					if ($OverrideDeath) { ?>
						<a href="<?php echo Globals::getCurrentSectorHREF(); ?>" class="buttonA">Let there be pod</a><?php
					} else { ?>
						<a href="<?php echo Globals::getCurrentSectorHREF(); ?>" class="buttonA">Current Sector</a><?php
					} ?>
				</div><?php
			} ?>
		</div>

		<?php
	}

}
