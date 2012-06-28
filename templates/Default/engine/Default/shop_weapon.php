<?php
if ($ThisLocation->isWeaponSold()) { ?>
	<table class="center standard">
		<tr>
			<th>Name</th>
			<th>Shield Damage</th>
			<th>Armour Damage</th>
			<th>Accuracy</th>
			<th>Race</th>
			<th>Power Level</th>
			<th>Cost</th>
			<th>Action</th>
		</tr><?php

		$WeaponsSold =& $ThisLocation->getWeaponsSold();
		foreach($WeaponsSold as &$Weapon) { ?>
			<tr>
				<td><?php echo $Weapon->getName(); ?></td>
				<td><?php echo $Weapon->getShieldDamage(); ?></td>
				<td><?php echo $Weapon->getArmourDamage(); ?></td>
				<td><?php echo $Weapon->getBaseAccuracy(); ?></td>
				<td><?php echo $Weapon->getRaceName(); ?></td>
				<td><?php echo $Weapon->getPowerLevel(); ?></td>
				<td><?php echo $Weapon->getCost(); ?></td>
				<td><a href="<?php echo $Weapon->getBuyHREF($ThisLocation); ?>" class="submitStyle">Buy</a></td>
				</td>
			</tr><?php
		} unset($Weapon);?>
	</table><?php
}

if ($ThisShip->hasWeapons()) { ?>
	<br /><br />
	<h1>Sell Weapons</h1><br />

	<table class="standard">
		<tr class="center">
			<th>Name</th>
			<th>Cash</th>
			<th>Action</th>
		</tr><?php
		$ShipWeapons =& $ThisShip->getWeapons();
		foreach ($ShipWeapons as $OrderID => &$Weapon) { ?>
			<tr class="center">
				<td><?php echo $Weapon->getName(); ?></td>
				<td><?php echo number_format(floor($Weapon->getCost() * WEAPON_REFUND_PERCENT)); ?></td>
				<td><a href="<?php echo $Weapon->getSellHREF($ThisLocation, $OrderID); ?>" class="submitStyle">Sell</a></td>
			</tr><?php
		} unset($Weapon); ?>
	</table><?php
} ?>