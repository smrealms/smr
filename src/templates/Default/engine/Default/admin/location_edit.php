<?php declare(strict_types=1);

if (!isset($Locations)) {
	?><a href="<?php echo $ViewAllLocationsLink; ?>">View All Locations</a><br /><br />
	<form action="<?php echo $Location->getEditHREF(); ?>" method="POST"><?php
} ?>
<table>
	<tr>
		<th>Name</th>
		<th>Action</th>
		<th>Image</th>
		<th>Fed</th>
		<th>Bar</th>
		<th>Bank</th>
		<th>HQ</th>
		<th>UG</th>
		<th>Hardware</th>
		<th>Ships</th>
		<th>Weapons</th>
		<th>Edit</th>
	</tr><?php
if (isset($Locations)) {
	$this->includeTemplate('admin/includes/ViewLocations.inc.php', ['Locations' => $Locations]);
} else { ?>
	<tr>
		<td><input name="name" type="text" value="<?php echo htmlspecialchars($Location->getName()); ?>" /></td>
		<td><input name="action" type="text" value="<?php echo htmlspecialchars($Location->getAction()); ?>" /></td>
		<td><input name="image" type="text" value="<?php echo htmlspecialchars($Location->getImage()); ?>" /></td>
		<td><input name="fed" type="checkbox" <?php if ($Location->isFed()) { ?>checked="checked"<?php } ?> /></td>
		<td><input name="bar" type="checkbox" <?php if ($Location->isBar()) { ?>checked="checked"<?php } ?> /></td>
		<td><input name="bank" type="checkbox" <?php if ($Location->isBank()) { ?>checked="checked"<?php } ?> /></td>
		<td><input name="hq" type="checkbox" <?php if ($Location->isHQ()) { ?>checked="checked"<?php } ?> /></td>
		<td><input name="ug" type="checkbox" <?php if ($Location->isUG()) { ?>checked="checked"<?php } ?> /></td>
		<td>
			<table><?php
				foreach ($Location->getHardwareSold() as $HardwareID => $Hardware) { ?>
					<tr>
						<td><?php echo $Hardware->name; ?></td>
						<td><input type="checkbox" name="remove_hardware[]" value="<?php echo $HardwareID; ?>" /></td>
					</tr><?php
				} ?>
				<tr>
					<td>Add Hardware:</td>
					<td>
						<select name="add_hardware_id">
							<option value="0">None</option><?php
							foreach ($AllHardware as $HardwareID => $Hardware) { ?>
								<option value="<?php echo $HardwareID; ?>"><?php echo $Hardware->name; ?></option><?php
							} ?>
					</select>
					</td>
				</tr>
			</table>
		</td>
		<td>
			<table><?php
			foreach ($Location->getShipsSold() as $ShipTypeSold) { ?>
					<tr>
						<td><?php echo $ShipTypeSold->getName(); ?></td>
						<td><input type="checkbox" name="remove_ships[]" value="<?php echo $ShipTypeSold->getTypeID(); ?>" /></td>
					</tr><?php
				} ?>
				<tr>
					<td>Add Ship:</td>
					<td>
						<select name="add_ship_id">
							<option value="0">None</option><?php
							foreach ($ShipTypes as $ShipType) { ?>
								<option value="<?php echo $ShipType->getTypeID(); ?>"><?php echo $ShipType->getName(); ?></option><?php
							} ?>
					</select>
					</td>
				</tr>
			</table>
		</td>
		<td>
			<table><?php
			foreach ($Location->getWeaponsSold() as $WeaponSold) { ?>
					<tr>
						<td><?php echo $WeaponSold->getName(); ?></td>
						<td><input type="checkbox" name="remove_weapons[]" value="<?php echo $WeaponSold->getWeaponTypeID(); ?>" /></td>
					</tr><?php
				} ?>
				<tr>
					<td>Add Weapon:</td>
					<td>
						<select name="add_weapon_id">
							<option value="0">None</option><?php
							foreach ($Weapons as $Weapon) {
								?><option value="<?php echo $Weapon->getWeaponTypeID(); ?>"><?php echo $Weapon->getName(); ?></option><?php
							} ?>
					</select>
					</td>
				</tr>
			</table>
		</td>
		<td>
			<?php echo create_submit('save', 'Save'); ?>
		</td>
	</tr><?php
} ?>
</table>
<?php
if (!isset($Locations)) {
	?></form><?php
}
