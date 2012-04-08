<?php $this->includeTemplate('includes/PortFullCombatResults.inc'); ?><br />
<br />
<div align="center"><?php
if(!$OverrideDeath && !$Port->isDestroyed()) { ?>
		<div style="width:50%">
			<div class="buttonA">
				<a href="<?php echo $Port->getAttackHREF() ?>" class="buttonA">Continue Attack</a>
			</div>
		</div><?php
	}
	else {
		if($OverrideDeath) {
			?><span class="red">You have been destroyed.</span><?php
		}
		else { ?>
			<span class="yellow">You have destroyed the port.</span><?php
		} ?><br />
		<div class="buttonA"><?php
		if($OverrideDeath) { ?>
				<a href="<?php echo Globals::getPodScreenHREF() ?>" class="buttonA">Let there be pod</a><?php
			}
			else { ?>
				<a href="<?php echo Globals::getCurrentSectorHREF() ?>" class="buttonA">Current Sector</a>&nbsp;
				<a href="<?php echo $Port->getClaimHREF() ?>" class="buttonA">Claim this port for your race</a>&nbsp;
				<a href="<?php echo $Port->getLootHREF() ?>" class="buttonA">Loot the port</a><?php
			} ?>
		</div><?php
	} ?>
</div>