<?php declare(strict_types=1);

/**
 * @var Smr\Player $TargetPlayer
 * @var Smr\Player $ThisPlayer
 * @var Smr\Sector $ThisSector
 * @var Smr\Ship $ThisShip
 * @var bool $NewbieKill
 */

$canAttack = false;

if ($ThisPlayer->hasNewbieTurns()) {
	?><p class="big green">You are under newbie protection!</p><?php
} elseif ($TargetPlayer->hasNewbieTurns()) {
	?><p class="big green">Your target is under newbie protection!</p><?php
} elseif ($ThisPlayer->sameAlliance($TargetPlayer)) {
	?><p class="big blue">This is your alliancemate.</p><?php
} elseif (!$ThisShip->canAttack()) {
	?><p class="big red">You ready your weapons, you take aim, you...realise you have no weapons.</p><?php
} elseif ($ThisPlayer->traderNAPAlliance($TargetPlayer)) {
	?><p class="big blue">This is your ally.</p><?php
} elseif ($ThisPlayer->hasFederalProtection()) {
	?><p class="big blue">You are under federal protection! That wouldn't be fair.</p><?php
} elseif ($TargetPlayer->hasFederalProtection()) {
	?><p class="big blue">Your target is under federal protection!</p><?php
} elseif (!$ThisPlayer->canSee($TargetPlayer)) {
	?><p class="big red">Your target has cloaked!</p><?php
} else {
	$canAttack = true;
	$fightingPlayers = $ThisSector->getFightingTraders($ThisPlayer, $TargetPlayer, true, allEligible: true);
	if (count($fightingPlayers['Defenders']) > 0) {
		if ($NewbieKill) { ?>
			<p class="big red">Your target is a new player, so you will not receive credit for killing them!</p><?php
		} ?>
		<p><a class="submitStyle" href="<?php echo $TargetPlayer->getAttackTraderHREF(); ?>">Attack Trader (<?php echo TURNS_TO_SHOOT_SHIP; ?>)</a></p><?php
	} else {
		?><p class="big red">You have no targets!</p><?php
	}
}
if (!$canAttack) {
	$fightingPlayers = $ThisSector->getPotentialFightingTraders($ThisPlayer);
}
?>

<table class="standard centered inset">
	<tr><?php
		foreach ($fightingPlayers as $label => $fleet) { ?>
			<th width="50%"><?php echo $label; ?> (<?php echo count($fleet); ?>)</th><?php
		} ?>
	</tr>
	<tr><?php
		foreach ($fightingPlayers as $fleet) {
			?><td class="top"><?php
			foreach ($fleet as $fleetPlayer) {
				$fleetShip = $fleetPlayer->getShip();
				if ($fleetPlayer->hasNewbieStatus()) { ?><span class="newbie"><?php }
				echo $fleetPlayer->getLevelName(); ?><br /><?php
				echo $fleetPlayer->getDisplayName() ?><br />
				Race: <?php echo $fleetPlayer->getRaceName() ?><br />
				Alliance: <?php echo $fleetPlayer->getAllianceDisplayName() ?><br /><br /><?php
				echo $fleetShip->getName() ?><br />
				Rating : <?php echo $fleetShip->getDisplayAttackRating() . '/' . $fleetShip->getDisplayDefenseRating() ?><br /><?php
				if ($ThisShip->hasScanner()) { ?>
					Shields : <?php echo $fleetShip->getShieldLow() . '-' . $fleetShip->getShieldHigh() ?><br />
					Armour : <?php echo $fleetShip->getArmourLow() . '-' . $fleetShip->getArmourHigh() ?><br />
					Hard Points: <?php echo $fleetShip->getNumWeapons() ?><br />
					Combat Drones: <?php echo $fleetShip->getCDsLow() . '-' . $fleetShip->getCDsHigh() ?><br /><?php
				}
				if ($fleetPlayer->hasNewbieStatus()) { ?></span><?php } ?>
				<br /><br /><?php
			} ?>
			</td><?php
		} ?>
	</tr>
</table>
