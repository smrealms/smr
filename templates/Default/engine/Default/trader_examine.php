<?php
$canAttack=false;

if($ThisPlayer->hasNewbieTurns()) {
	?><p><big class="green">You are under newbie protection!</big></p><?php
}
else if($TargetPlayer->hasNewbieTurns()) {
	?><p><big class="green">Your target is under newbie protection!</big></p><?php
}
else if($ThisPlayer->sameAlliance($TargetPlayer)) {
	?><p><big class="blue">This is your alliancemate.</big></p><?php
}
else if(!$ThisShip->canAttack()) {
	?><p><big class="red">You ready your weapons, you take aim, you...realise you have no weapons.</big></p><?php
}
else if($ThisPlayer->traderNAPAlliance($TargetPlayer)) {
	?><p><big class="blue">This is your ally.</big></p><?php
}
else if($ThisPlayer->hasFederalProtection()) {
	?><p><big class="blue">You are under federal protection! That wouldn't be fair.</big></p><?php
}
else if($TargetPlayer->hasFederalProtection()) {
	?><p><big class="blue">Your target is under federal protection!</big></p><?php
}
else {
	$canAttack=true;
	$fightingPlayers = $ThisSector->getFightingTraders($ThisPlayer,$TargetPlayer, true);
	if(count($fightingPlayers['Defenders'])>0) {
		?><p><a class="submitStyle" href="<?php echo $TargetPlayer->getAttackTraderHREF(); ?>">Attack Trader (3)</a></p><?php
	}
	else {
		?><p><big class="red">You have no targets!</big></p><?php
	}
}
if(!$canAttack)
	$fightingPlayers = $ThisSector->getPotentialFightingTraders($ThisPlayer);
$fightingPlayers['Attackers'][$ThisPlayer->getAccountID()] = $ThisPlayer;
?>
<div align="center">
	<table class="standard" width="95%">
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
						Level: <?php echo $fleetPlayer->getLevelName() ?><br />
						Alliance: <?php echo $fleetPlayer->getAllianceName() ?><br /><br /><?php
						echo $fleetShip->getName() ?><br />
						Rating : <?php echo $fleetShip->getDisplayAttackRating($ThisPlayer) .'/'. $fleetShip->getDisplayDefenseRating($ThisPlayer) ?><br /><?php
						if ($ThisShip->hasScanner()) { ?>
							Shields : <?php echo $fleetShip->getShieldLow() . '-' . $fleetShip->getShieldHigh() ?><br />
							Armour : <?php echo $fleetShip->getArmourLow() . '-' . $fleetShip->getArmourHigh() ?><br />
							Hard Points: <?php echo $fleetShip->getNumWeapons() ?><br />
							Combat Drones: <?php echo $fleetShip->getCDsLow() . '-' . $fleetShip->getCDsHigh() ?><br /><?php
						}
						if ($fleetPlayer->hasNewbieStatus()) { ?></span><?php } ?>
						<br /><br /><?php
					}
				}
				else {
					?>&nbsp;<?php
				} ?>
				</td><?php
			}
			if(!$canAttack) {
				?><td>&nbsp;</td><?php
			} ?>
		</tr>
	</table>
</div>
