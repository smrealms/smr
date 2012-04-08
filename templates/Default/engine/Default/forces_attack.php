<?php $this->includeTemplate('includes/ForceFullCombatResults.inc'); ?><br />
<br />
<div align="center"><?php
	if(isset($Target)) { ?>
		<div style="width:50%">
			<div class="buttonA">
				<a href="<?php echo $Target->getAttackForcesHREF() ?>" class="buttonA">Continue Attack</a>
			</div>
		</div><?php
	}
	else {
		if($OverrideDeath) {
			?><span class="red">You have been destroyed.</span><?php
		}
		else {
			?><span class="yellow">You have destroyed the forces.</span><?php
		} ?><br />
		<div class="buttonA"><?php
		if($OverrideDeath) {
			?><a href="<?php echo Globals::getPodScreenHREF() ?>" class="buttonA">Let there be pod</a><?php
		}
		else {
			?><a href="<?php echo Globals::getCurrentSectorHREF() ?>" class="buttonA">Current Sector</a><?php
		} ?>
		</div><?php
	} ?>
</div>