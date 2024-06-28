<?php declare(strict_types=1);

use Smr\Globals;
use Smr\Port;
use Smr\PortPayoutType;

/**
 * @var Smr\Port $Port
 * @var Smr\Template $this
 * @var bool $OverrideDeath
 * @var bool $CreditedAttacker
 */

$this->includeTemplate('includes/PortFullCombatResults.inc.php'); ?><br />
<br />
<div class="center"><?php
	if (!$OverrideDeath && !$Port->isBusted()) { ?>
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
		<br /><br /><?php
		if ($Port->getCredits() > 0) { ?>
			How shall your victory be marked? Choose one of the following:<br /><br />
			<div class="buttonA">
				<a href="<?php echo $Port->getPayoutHREF(PortPayoutType::Loot); ?>" class="buttonA">Loot (100% credits)</a><br /><br />
				<a href="<?php echo $Port->getPayoutHREF(PortPayoutType::Raze); ?>" class="buttonA">Raze (<?php echo IRound(Port::RAZE_PAYOUT * 100); ?>% credits, 1 downgrade)</a><br /><br />
				<a href="<?php echo $Port->getPayoutHREF(PortPayoutType::Claim); ?>" class="buttonA">Claim for your race (<?php echo IRound(Port::CLAIM_PAYOUT * 100); ?>% credits)</a><br /><br /><?php
				if ($Port->canBeDestroyed()) { ?>
					<a href="<?php echo $Port->getPayoutHREF(PortPayoutType::Destroy); ?>" class="buttonA">Destroy (no credits, only oblivion)</a><?php
				} ?>
			</div><?php
		}
	} ?>
</div>
