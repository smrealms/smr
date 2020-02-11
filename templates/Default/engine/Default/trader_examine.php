<?php
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
} else {
	$canAttack = true;
	$fightingPlayers = $ThisSector->getFightingTraders($ThisPlayer, $TargetPlayer, true);
	if (count($fightingPlayers['Defenders']) > 0) {
		?><p><a class="submitStyle" href="<?php echo $TargetPlayer->getAttackTraderHREF(); ?>">Attack Trader (3)</a></p><?php
	} else {
		?><p class="big red">You have no targets!</p><?php
	}
}
if (!$canAttack) {
	$fightingPlayers = $ThisSector->getPotentialFightingTraders($ThisPlayer);
}
$fightingPlayers['Attackers'][$ThisPlayer->getAccountID()] = $ThisPlayer;
?>

<table class="standard centered inset">
	<tr><th width="50%">Attacker</th><th width="50%">Defender</th></tr>
	<tr><?php
		foreach ($fightingPlayers as $fleet) {
			?><td class="top"><?php
			if (is_array($fleet)) {
				foreach ($fleet as $fleetPlayer) {
					$fleetShip = $fleetPlayer->getShip();
					if ($fleetPlayer->hasNewbieStatus()) { ?><span class="newbie"><?php }
					echo $fleetPlayer->getLevelName(); ?><br /><?php
					echo $fleetPlayer->getDisplayName() ?><br />
					Race: <?php echo $fleetPlayer->getRaceName() ?><br />
					Alliance: <?php echo $fleetPlayer->getAllianceDisplayName() ?><br /><br /><?php
					echo $fleetShip->getName() ?><br />
					Rating : <?php echo $fleetShip->getDisplayAttackRating($ThisPlayer) . '/' . $fleetShip->getDisplayDefenseRating($ThisPlayer) ?><br /><?php
					if ($ThisShip->hasScanner()) { ?>
						Shields : <?php echo $fleetShip->getShieldLow() . '-' . $fleetShip->getShieldHigh() ?><br />
						Armour : <?php echo $fleetShip->getArmourLow() . '-' . $fleetShip->getArmourHigh() ?><br />
						Hard Points: <?php echo $fleetShip->getNumWeapons() ?><br />
						Combat Drones: <?php echo $fleetShip->getCDsLow() . '-' . $fleetShip->getCDsHigh() ?><br /><?php
					}
					if ($fleetPlayer->hasNewbieStatus()) { ?></span><?php } ?>
					<br /><br /><?php
				}
			} else {
				?>&nbsp;<?php
			} ?>
			</td><?php
		}
		if (!$canAttack) {
			?><td>&nbsp;</td><?php
		} ?>
	</tr>
</table>
