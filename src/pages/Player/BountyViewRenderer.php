<?php declare(strict_types=1);

use Smr\BountyType;

/**
 * @var Smr\Player $BountyPlayer
 */

if ($BountyPlayer->hasBounties()) {
	$Bounties = $BountyPlayer->getBounties();
	foreach (BountyType::cases() as $BountyType) {
		foreach ($Bounties as $Bounty) {
			if ($Bounty->type !== $BountyType) {
				continue;
			}
			if ($Bounty->type === BountyType::HQ) { ?>
				The <span class="green">Federal Government</span><?php
			} elseif ($Bounty->type === BountyType::UG) { ?>
				The <span class="red">Underground</span><?php
			} ?>
			is offering a bounty on <?php echo $BountyPlayer->getDisplayName(); ?> worth <span class="creds"><?php echo number_format($Bounty->getCredits()); ?></span> credits and <span class="yellow"><?php echo $Bounty->getSmrCredits(); ?></span> SMR credits.<br /><?php
			if (!$Bounty->isActive()) { ?>
				This bounty can be claimed by <?php echo $Bounty->getClaimerPlayer()->getDisplayName(); ?>.<br /><?php
			} ?>
			<br /><?php
		}
	}
} else {
	echo $BountyPlayer->getDisplayName(); ?> has no bounties<br /><?php
}
