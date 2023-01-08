<?php declare(strict_types=1);

use Smr\Globals;

/**
 * @var Smr\Ship $ThisShip
 * @var Smr\Template $this
 * @var bool $OverrideDeath
 */

$this->includeTemplate('includes/ForceFullCombatResults.inc.php'); ?><br />
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
