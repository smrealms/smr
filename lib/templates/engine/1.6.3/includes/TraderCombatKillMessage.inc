<?php
echo $TargetPlayer->getDisplayName() ?> has been <span class="red">DESTROYED!</span><br /><?php
if($KillResults)
{
	echo $ShootingPlayer->getDisplayName() ?> salvages <span class="yellow"><?php echo number_format($KillResults['KillerCredits']) ?></span> credits from the wreckage and gains <span class="blue"><?php echo number_format($KillResults['KillerExp']) ?></span> experience.<br /><?php
} ?>