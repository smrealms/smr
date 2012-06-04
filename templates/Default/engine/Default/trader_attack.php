<?php $this->includeTemplate('includes/TraderFullCombatResults.inc'); ?><br />
<br />
<div align="center"><?php
	if(isset($Target))
	{ ?>
		<div style="width:50%" align="<?php if($RandomPosition == 0){ ?>center<?php }else if($RandomPosition == 1){ ?>right<?php }else{ ?>left<?php } ?>">
			<div class="buttonA">
				<a href="<?php echo $Target->getAttackTraderHREF() ?>" class="buttonA">Continue Attack</a>
			</div>
		</div><?php
	}
	else
	{ ?>
		<h2>The battle has ended!</h2><br />
		<div class="buttonA"><?php
			if($OverrideDeath)
			{ ?>
				<a href="<?php echo Globals::getPodScreenHREF() ?>" class="buttonA">Let there be pod</a><?php
			}
			else
			{ ?>
				<a href="<?php echo Globals::getCurrentSectorHREF() ?>" class="buttonA">Current Sector</a><?php
			} ?>
		</div><?php
	} ?>
</div>