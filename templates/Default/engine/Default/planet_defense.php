<?php if ($ThisPlanet->getMaxShields()+$ThisPlanet->getMaxCDs()+$ThisPlanet->getMaxArmour() == 0) { ?>
	<p>This planet cannot store any defensive armaments.</p>
<?php } else { ?>	

<table class="standard">
	<tr>
		<th>Type</th>
		<th>Ship</th>
		<th>Planet</th>
		<th>Amount</th>
		<th>Transfer to</th>
	</tr>
	
	<?php if ($ThisPlanet->getMaxShields() > 0) { ?>
	<form name="TransferShieldsForm" method="POST" action="<?php echo $TransferShieldsHref; ?>">
		<tr>
			<td><img id="shields" src="images/shields.png"  width="16" height="16" alt="" title="Shields"/>Shields</td>
			<td align="center"><?php echo $ThisShip->getShields(); ?></td>
			<td align="center"><?php echo $ThisPlanet->getShields(); ?></td>
			<td align="center"><input type="number" name="amount" value="<?php echo min($ThisShip->getShields(),$ThisPlanet->getMaxShields()-$ThisPlanet->getShields()); ?>" id="InputFields" size="4" class="center"></td>
			<td>
				<input type="submit" name="action" value="Ship" id="InputFields" />&nbsp;<input type="submit" name="action" value="Planet" id="InputFields" />
			</td>
		</tr>
	</form>
	<?php } if ($ThisPlanet->getMaxCDs() > 0) { ?>
	<form name="TransferCDsForm" method="POST" action="<?php echo $TransferCDsHref; ?>">
		<tr>
			<td><img id="cds" src="images/cd.png"  width="16" height="16" alt="" title="Combat Drones"/>Combat Drones</td>
			<td align="center"><?php echo $ThisShip->getCDs(); ?></td>
			<td align="center"><?php echo $ThisPlanet->getCDs(); ?></td>
			<td align="center"><input type="number" name="amount" value="<?php echo min($ThisShip->getCDs(),$ThisPlanet->getMaxCDs()-$ThisPlanet->getCDs()); ?>" id="InputFields" size="4" class="center"></td>
			<td>
				<input type="submit" name="action" value="Ship" id="InputFields" />&nbsp;<input type="submit" name="action" value="Planet" id="InputFields" />
			</td>
		</tr>
	</form>
	<?php } ?>
	<?php if ($ThisPlanet->getMaxArmour() > 0) { ?>
	<form name="TransferArmourForm" method="POST" action="<?php echo $TransferArmourHref; ?>">
		<tr>
			<td><img id="turret" src="images/armour.png"  width="16" height="16" alt="" title="Armour"/>Armour</td>
			<td align="center"><?php echo $ThisShip->getArmour(); ?></td>
			<td align="center"><?php echo $ThisPlanet->getArmour(); ?></td>
			<td align="center"><input type="number" name="amount" value="<?php echo min($ThisShip->getArmour()-1,$ThisPlanet->getMaxArmour()-($ThisPlanet->getArmour())); ?>" id="InputFields" size="4" class="center"></td>
			<td>
				<input type="submit" name="action" value="Ship" id="InputFields" />&nbsp;<input type="submit" name="action" value="Planet" id="InputFields" />
			</td>
		</tr>
	</form>
	<?php } ?>

</table>
<?php } ?>