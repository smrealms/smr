<?php
echo $TargetPort->getDisplayName() ?>'s defenses are <span class="red">DESTROYED!</span><br /><?php
if (isset($KillResults['KillerCredits'])) {
	echo $ShootingPlayer->getDisplayName() ?> claims <span class="creds"><?php echo number_format($KillResults['KillerCredits']) ?></span> credits from the port.<br /><?php
} ?>
