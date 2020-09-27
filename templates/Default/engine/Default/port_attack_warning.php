<span class="red">WARNING WARNING</span> port assault about to commence!!<br />

<br />
Suddenly, sirens sound and warning lights flash as your onboard sensors detect
that the port has enough defensive firepower to reduce your ship to space debris.
Without an armada behind you, the outcome may not be pleasant.
<a href="<?php echo WIKI_URL; ?>/game-guide/combat#ports" target="_blank"><img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Port Combat"/></a>
<br /><br />

<?php
if ($ThisShip->hasScanner()) { ?>
	<table class="standard">
		<tr>
			<th>Port</th>
			<th>Scan Results</th>
		</tr>
		<tr>
			<td>Shields</td>
			<td id="port_shields" class="center ajax"><?php echo $Port->getShields(); ?></td>
		</tr>
		<tr>
			<td>Combat Drones</td>
			<td id="port_cds" class="center ajax"><?php echo $Port->getCDs(); ?></td>
		</tr>
		<tr>
			<td>Armour</td>
			<td id="port_armour" class="center ajax"><?php echo $Port->getArmour(); ?></td>
		</tr>
		<tr>
			<td>Turrets</td>
			<td id="port_turrets" class="center ajax"><?php echo $Port->getNumWeapons(); ?></td>
		</tr>
	</table>
	<br /><?php
} ?>

Are you sure you want to attack this port?<br /><br />

<div class="buttonA">
	<a class="buttonA" href="<?php echo $PortAttackHREF; ?>">Yes</a>
</div>&nbsp;
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getCurrentSectorHREF(); ?>">No</a>
</div>

<br /><br />
<span id="reinforce" class="red"><?php
if ($Port->isUnderAttack()) { ?>
	The port is under attack and has activated its distress beacon!<br />
	Federal reinforcements will arrive to defend the port in
	<?php echo format_time($Port->getReinforceTime() - TIME) . '.';
}
?></span>

<?php
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