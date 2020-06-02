<?php
if ($BountyPlayer->hasBounties()) {
	$Bounties = $BountyPlayer->getBounties();
	$HasHQBounty = false;
	foreach ($Bounties as $Bounty) {
		if ($Bounty['Type'] == 'HQ') { ?>
			The <span class="green">Federal Government</span> is offering a bounty on <?php echo $BountyPlayer->getDisplayName(); ?> worth <span class="creds"><?php echo $Bounty['Amount']; ?></span> credits and <span class="yellow"><?php echo $Bounty['SmrCredits']; ?></span> SMR credits.<br /><?php
			if ($Bounty['Claimer'] != 0) { ?>
				This bounty can be claimed by <?php echo SmrPlayer::getPlayer($Bounty['Claimer'], $ThisPlayer->getGameID())->getDisplayName(); ?><br /><?php
				$HasHQBounty = true;
			}
		}
	}
	if ($HasHQBounty) {
		?><br /><br /><br /><?php
	}
	foreach ($Bounties as $Bounty) {
		if ($Bounty['Type'] == 'UG') { ?>
			The <span class="red">Underground</span> is offering a bounty on <?php echo $BountyPlayer->getDisplayName(); ?> worth <span class="creds"><?php echo $Bounty['Amount']; ?></span> credits and <span class="yellow"><?php echo $Bounty['SmrCredits']; ?></span> SMR credits.<br /><?php
			if ($Bounty['Claimer'] != 0) {
				?>This bounty can be claimed by <?php echo SmrPlayer::getPlayer($Bounty['Claimer'], $ThisPlayer->getGameID())->getDisplayName(); ?><br /><?php
			}
		}
	}
} else {
	echo $BountyPlayer->getDisplayName(); ?> has no bounties<br /><?php
} ?>
