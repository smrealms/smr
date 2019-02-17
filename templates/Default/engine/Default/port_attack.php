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
			<span class="yellow">You have breached the port defenses.</span><?php
		} ?>
		<br /><br />
		<div class="buttonA"><?php
			if($OverrideDeath) { ?>
				<a href="<?php echo Globals::getPodScreenHREF() ?>" class="buttonA">Let there be pod</a><?php
			}
			else { ?>
				<a href="<?php echo $Port->getClaimHREF(); ?>" class="buttonA">Claim this port for your race</a><?php
				if($Port->getCredits() > 0) { ?>&nbsp;
					<a href="<?php echo $Port->getLootHREF(); ?>" class="buttonA">Loot the port<?php if($Port->getCredits() > 0) { ?> (100% money)<?php } ?></a>&nbsp;
					<a href="<?php echo $Port->getRazeHREF(); ?>" class="buttonA">Raze the port (<?php echo SmrPort::RAZE_MONEY_PERCENT; ?>% money, 1 downgrade)</a><?php
				}
			} ?>
		</div><?php
	} ?>
</div>
