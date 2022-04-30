<?php $this->includeTemplate('includes/PortFullCombatResults.inc.php'); ?><br />
<br />
<div class="center"><?php
	if (!$OverrideDeath && !$Port->isDestroyed()) { ?>
		<div class="buttonA">
			<a href="<?php echo $Port->getAttackHREF() ?>" class="buttonA">Continue Attack</a>
		</div><?php
	} elseif ($OverrideDeath) { ?>
		<span class="red">You have been destroyed.</span>
		<br /><br />
		<div class="buttonA">
			<a href="<?php echo Globals::getCurrentSectorHREF() ?>" class="buttonA">Let there be pod</a>
		</div><?php
	} elseif ($CreditedAttacker) { ?>
		<span class="yellow">You have breached the port defenses.</span>
		<br /><br />
		<div class="buttonA">
			<a href="<?php echo $Port->getClaimHREF(); ?>" class="buttonA">Claim this port for your race</a><?php
			if ($Port->getCredits() > 0) { ?>&nbsp;
				<a href="<?php echo $Port->getLootHREF(); ?>" class="buttonA">Loot the port (100% money)</a>&nbsp;
				<a href="<?php echo $Port->getRazeHREF(); ?>" class="buttonA">Raze the port (<?php echo IRound(SmrPort::RAZE_PAYOUT * 100); ?>% money, 1 downgrade)</a><?php
			} ?>
		</div><?php
	} ?>
</div>
