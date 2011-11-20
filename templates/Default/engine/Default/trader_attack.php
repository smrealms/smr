<?php $this->includeTemplate('includes/TraderFullCombatResults.inc'); ?><br />
<br />
<div align="center"><?php
	if(isset($Target))
	{ ?>
		<div class="buttonA">
			<a href="<?php echo $Target->getAttackTraderHREF(); ?>" class="buttonA">Continue&nbsp;Attack</a>
		</div>><?php
	}
	else
	{ ?>
		<h2>The battle has ended!</h2><br />
		<div class="buttonA"><?php
			if($OverrideDeath)
			{ ?>
				<a href="<?php echo Globals::getPodScreenHREF(); ?>" class="buttonA">Let there be pod</a><?php
			}
			else
			{ ?>
				<a href="<?php echo Globals::getCurrentSectorHREF(); ?>" class="buttonA">Current Sector</a><?php
			} ?>
		</div><?php
	} ?>
</div>