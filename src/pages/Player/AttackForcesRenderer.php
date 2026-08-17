<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractShip;
use Smr\Combat\Results\ForceFullCombatResults;
use Smr\Force;
use Smr\Globals;
use Smr\Pages\Shared\ForceFullCombatResultsRenderer;
use Smr\Template;

class AttackForcesRenderer {

public static function render(Template $template, ForceFullCombatResults $FullForceCombatResults, ?Force $Target, bool $OverrideDeath, AbstractShip $ThisShip): void {
ForceFullCombatResultsRenderer::render(
	template: $template,
	FullForceCombatResults: $FullForceCombatResults,
); ?><br />
<br />
<div class="center"><?php
	if (isset($Target)) { ?>
		<div class="buttonA">
			<a href="<?php echo $Target->getAttackForcesHREF() ?>" class="buttonA">Continue Attack (<?php echo $Target->getAttackTurnCost($ThisShip); ?>)</a>
		</div><?php
	} else {
		if ($OverrideDeath) {
			?><span class="red">You have been destroyed.</span><?php
		} else {
			?><span class="yellow">You have destroyed the forces.</span><?php
		} ?>
		<br /><br />
		<div class="buttonA"><?php
		if ($OverrideDeath) {
			?><a href="<?php echo Globals::getCurrentSectorHREF() ?>" class="buttonA">Let there be pod</a><?php
		} else {
			?><a href="<?php echo Globals::getCurrentSectorHREF() ?>" class="buttonA">Current Sector</a><?php
		} ?>
		</div><?php
	} ?>
</div>

<?php
}

}
