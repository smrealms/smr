<?php
if ($BountyPlayer->hasBounties()) {
	$Bounties = $BountyPlayer->getBounties();
	foreach ($Bounties as $Bounty) {
		if ($Bounty['Type'] == 'HQ') { ?>
			The <span class="green">Federal Government</span> is offering a bounty on <?php echo $BountyPlayer->getDisplayName(); ?> worth <span class="creds"><?php echo number_format($Bounty['Amount']); ?></span> credits and <span class="yellow"><?php echo $Bounty['SmrCredits']; ?></span> SMR credits.<br /><?php
			if ($Bounty['Claimer'] != 0) { ?>
				This bounty can be claimed by <?php echo SmrPlayer::getPlayer($Bounty['Claimer'], $ThisPlayer->getGameID())->getDisplayName(); ?>.<br /><?php
			} ?>
			<br /><?php
		}
	}

	foreach ($Bounties as $Bounty) {
		if ($Bounty['Type'] == 'UG') { ?>
			The <span class="red">Underground</span> is offering a bounty on <?php echo $BountyPlayer->getDisplayName(); ?> worth <span class="creds"><?php echo number_format($Bounty['Amount']); ?></span> credits and <span class="yellow"><?php echo $Bounty['SmrCredits']; ?></span> SMR credits.<br /><?php
			if ($Bounty['Claimer'] != 0) {
				?>This bounty can be claimed by <?php echo SmrPlayer::getPlayer($Bounty['Claimer'], $ThisPlayer->getGameID())->getDisplayName(); ?>.<br /><?php
			} ?>
			<br /><?php
		}
	}
} else {
	echo $BountyPlayer->getDisplayName(); ?> has no bounties<br /><?php
} ?>
