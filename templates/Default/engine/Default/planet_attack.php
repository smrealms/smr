<?php $this->includeTemplate('includes/PlanetFullCombatResults.inc'); ?><br />
<br />
<div class="center"><?php
if(!$OverrideDeath && !$Planet->isDestroyed()) { ?>
	<div class="buttonA">
		<a href="<?php echo $Planet->getAttackHREF() ?>" class="buttonA">Continue Attack</a>
	</div><?php
}
else {
	if($OverrideDeath) {
		?><span class="red">You have been destroyed.</span><?php
	}
	else {
		?><span class="yellow">You have breached the planetary defenses.</span><?php
	} ?>
	<br /><br />
	<div class="buttonA"><?php
		if($OverrideDeath) {
			?><a href="<?php echo Globals::getPodScreenHREF() ?>" class="buttonA">Let there be pod</a><?php
		}
		else { ?>
			<a href="<?php echo Globals::getCurrentSectorHREF() ?>" class="buttonA">Current Sector</a>&nbsp;
			<a href="<?php echo $Planet->getLandHREF(); ?>" class="buttonA">Land on Planet (1)</a><?php
		} ?>
	</div><?php
} ?>
</div>
