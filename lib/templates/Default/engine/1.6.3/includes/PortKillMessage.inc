<?php
echo $TargetPort->getDisplayName() ?>'s defenses are <span class="red">DESTROYED!</span><br /><?php
if($KillResults)
{
	echo $ShootingPlayer->getDisplayName() ?> claims <span class="yellow"><?php echo number_format($KillResults['KillerCredits']) ?></span> credits from the port.<br /><?php
} ?>