<?php
echo $TargetPlayer->getDisplayName(); ?> has been <span class="red">DESTROYED</span>, losing <span class="exp"><?php echo number_format($KillResults['DeadExp'])?></span> experience.<br /><?php
if(isset($ShootingPlayer)) {
	// Killed by another player
	echo $ShootingPlayer->getDisplayName(); ?> salvages <span class="creds"><?php echo number_format($KillResults['KillerCredits']); ?></span> credits from the wreckage and gains <span class="exp"><?php echo number_format($KillResults['KillerExp']); ?></span> experience.<br /><?php
} else {
	// Killed by port, planet, forces
	echo 'The <span class="creds"> ' . number_format($KillResults['LostCredits'])
	     . '</span> credits that were onboard ' . $TargetPlayer->getDisplayName()
	     . "'s ship are lost in the wreckage.<br />";
}
?>
