<?php if ($ThisPlanet->getMaxShields() + $ThisPlanet->getMaxCDs() + $ThisPlanet->getMaxArmour() == 0) { ?>
	<p>This planet cannot yet store any shields, combat drones, or armour.</p>
<?php } else { ?>	

<br />
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
			<td><img src="images/shields.png"  width="16" height="16" alt="" title="Shields"/>Shields</td>
			<td class="center"><?php echo $ThisShip->getShields(); ?></td>
			<td class="center"><?php echo $ThisPlanet->getShields(); ?></td>
			<td class="center"><input type="number" name="amount" value="<?php echo min($ThisShip->getShields(), $ThisPlanet->getMaxShields() - $ThisPlanet->getShields()); ?>" class="center" size="4"></td>
			<td>
				<input type="submit" name="action" value="Ship" />&nbsp;<input type="submit" name="action" value="Planet" />
			</td>
		</tr>
	</form>
	<?php } if ($ThisPlanet->getMaxCDs() > 0) { ?>
	<form name="TransferCDsForm" method="POST" action="<?php echo $TransferCDsHref; ?>">
		<tr>
			<td><img src="images/cd.png"  width="16" height="16" alt="" title="Combat Drones"/>Combat Drones</td>
			<td class="center"><?php echo $ThisShip->getCDs(); ?></td>
			<td class="center"><?php echo $ThisPlanet->getCDs(); ?></td>
			<td class="center"><input type="number" name="amount" value="<?php echo min($ThisShip->getCDs(), $ThisPlanet->getMaxCDs() - $ThisPlanet->getCDs()); ?>" class="center" size="4"></td>
			<td>
				<input type="submit" name="action" value="Ship" />&nbsp;<input type="submit" name="action" value="Planet" />
			</td>
		</tr>
	</form>
	<?php } ?>
	<?php if ($ThisPlanet->getMaxArmour() > 0) { ?>
	<form name="TransferArmourForm" method="POST" action="<?php echo $TransferArmourHref; ?>">
		<tr>
			<td><img src="images/armour.png"  width="16" height="16" alt="" title="Armour"/>Armour</td>
			<td class="center"><?php echo $ThisShip->getArmour(); ?></td>
			<td class="center"><?php echo $ThisPlanet->getArmour(); ?></td>
			<td class="center"><input type="number" name="amount" value="<?php echo min($ThisShip->getArmour() - 1, $ThisPlanet->getMaxArmour() - ($ThisPlanet->getArmour())); ?>" class="center" size="4"></td>
			<td>
				<input type="submit" name="action" value="Ship" />&nbsp;<input type="submit" name="action" value="Planet" />
			</td>
		</tr>
	</form>
	<?php } ?>

</table>
<?php }

if ($ThisPlanet->getMaxMountedWeapons() > 0) { ?>
	<p>You can uninstall weapons from your ship and mount them on the planet. Once mounted, a weapon cannot be removed without destroying it. The weapons will fire in the order specified here.</p>
	<form method="POST" action="<?php echo $WeaponProcessingHREF; ?>">
		<table class="standard">
			<tr>
				<th>Order</th>
				<th>Reorder</th>
				<th>Weapon</th>
				<th>Damage</th>
				<th>Base<br />Accuracy</th>
				<th>Power<br />Level</th>
				<th>Action</th>
			</tr><?php
			$weapons = $ThisPlanet->getMountedWeapons();
			for ($i = 0; $i < $ThisPlanet->getMaxMountedWeapons(); ++$i) { ?>
				<tr class="center">
					<td><?php echo $i + 1; ?></td>
					<td><?php
						if ($i != 0) { ?>
							<button type="submit" title="Move Up" style="padding:0px; height:20px; border:none;" name="move_up" value="<?php echo $i; ?>"><img src="images/up.gif" alt="" height="20" width="20" /></button><?php
						}
						if ($i != $ThisPlanet->getMaxMountedWeapons() - 1) { ?>
							<button type="submit" title="Move Down" style="padding:0px; height:20px; border:none;" name="move_down" value="<?php echo $i; ?>"><img src="images/down.gif" alt="" height="20" width="20" /></button><?php
					} ?>
					</td><?php
					if (isset($weapons[$i])) { ?>
						<td class="left"><?php echo $weapons[$i]->getName(); ?></td>
						<td><?php echo $weapons[$i]->getShieldDamage() . ' / ' . $weapons[$i]->getArmourDamage(); ?></td>
						<td><?php echo $weapons[$i]->getBaseAccuracy(); ?>%</td>
						<td><?php echo $weapons[$i]->getPowerLevel(); ?></td>
						<td><?php
							if (count($weapons) == $ThisPlanet->getMaxMountedWeapons()) {
								// Only allow destroying mounted weapons when all slots are filled ?>
								<button type="submit" name="destroy" value="<?php echo $i; ?>">Destroy</button><?php
							} ?>
						</td><?php
					} else { ?>
						<td class="left">
							<select name="ship_order<?php echo $i; ?>" onchange="showWeaponInfo(this)" data-target=".weapon-info<?php echo $i; ?>"><?php
								foreach ($ThisShip->getWeapons() as $orderID => $weapon) { ?>
									<option value="<?php echo $orderID; ?>" data-show=".weapon<?php echo $i . '-' . $orderID; ?>"><?php echo $weapon->getName(); ?></option><?php
								} ?>
								<option disabled selected value="">Select Weapon</option>
							</select>
						</td>
						<td>
							<div class="weapon-info<?php echo $i; ?>"><?php
								foreach ($ThisShip->getWeapons() as $orderID => $weapon) { ?>
									<div class="weapon<?php echo $i . '-' . $orderID; ?> hide"><?php echo $weapon->getShieldDamage() . ' / ' . $weapon->getArmourDamage(); ?></div><?php
								} ?>
							</div>
						</td>
						<td>
							<div class="weapon-info<?php echo $i; ?>"><?php
								foreach ($ThisShip->getWeapons() as $orderID => $weapon) { ?>
									<div class="weapon<?php echo $i . '-' . $orderID; ?> hide"><?php echo $weapon->getBaseAccuracy(); ?>%</div><?php
								} ?>
							</div>
						</td>
						<td>
							<div class="weapon-info<?php echo $i; ?>"><?php
								foreach ($ThisShip->getWeapons() as $orderID => $weapon) { ?>
									<div class="weapon<?php echo $i . '-' . $orderID; ?> hide"><?php echo $weapon->getPowerLevel(); ?></div><?php
								} ?>
							</div>
						<td><button type="submit" name="transfer" value="<?php echo $i; ?>">Transfer</button></td><?php
					} ?>
				</tr><?php
			} ?>
		</table>
	</form><?php
} ?>
