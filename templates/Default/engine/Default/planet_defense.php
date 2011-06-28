<table class="standard">
	<tr>
		<th>Type</th>
		<th>Ship</th>
		<th>Planet</th>
		<th>Amount</th>
		<th>Transfer to</th>
	</tr>
	
	<form name="TransferShieldsForm" method="POST" action="<?php echo $TransferShieldsHref; ?>">
		<tr>
			<td>Shields</td>
			<td align="center"><?php echo $ThisShip->getShields(); ?></td>
			<td align="center"><?php echo $ThisPlanet->getShields(); ?></td>
			<td align="center"><input type="text" name="amount" value="<?php echo min($ThisShip->getShields(),$ThisPlanet->getMaxShields()-$ThisPlanet->getShields()); ?>" id="InputFields" size="4" class="center"></td>
			<td>
				<input type="submit" name="action" value="Ship" id="InputFields" />&nbsp;<input type="submit" name="action" value="Planet" id="InputFields" />
			</td>
		</tr>
	</form>
	
	<form name="TransferCDsForm" method="POST" action="<?php echo $TransferCDsHref; ?>">
		<tr>
			<td>Combat Drones</td>
			<td align="center"><?php echo $ThisShip->getCDs(); ?></td>
			<td align="center"><?php echo $ThisPlanet->getCDs(); ?></td>
			<td align="center"><input type="text" name="amount" value="<?php echo min($ThisShip->getCDs(),$ThisPlanet->getMaxCDs()-$ThisPlanet->getCDs()); ?>" id="InputFields" size="4" class="center"></td>
			<td>
				<input type="submit" name="action" value="Ship" id="InputFields" />&nbsp;<input type="submit" name="action" value="Planet" id="InputFields" />
			</td>
		</tr>
	</form>

</table>