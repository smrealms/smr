<a href="<?php echo $CombatSimLink; ?>">Combat Simulator</a><br /><br />

<form action="<?php echo $SelectDummysLink; ?>" method="POST">
	Edit Dummy:
	<select name="dummy_name"><?php
		foreach ($DummyNames as $DummyName) {
			?><option value="<?php echo $DummyName; ?>"<?php if ($DummyName == $DummyPlayer->getPlayerName()) { ?> selected="selected"<?php } ?>><?php echo $DummyName; ?></option><?php
		} ?>
	</select><br />
	<input type="submit" value="Select Dummy" />
</form>

<table>
	<tr>
		<td class="top">
			<span class="underline"><?php echo $DummyPlayer->getPlayerName(); ?></span><br /><br />
			<form action="<?php echo $EditDummysLink; ?>" method="POST">
				<input type="text" name="dummy_name" value="<?php echo $DummyPlayer->getPlayerName() ?>" />
				Level
				<select name="exp">
					<?php foreach ($Levels as $LevelID => $Level) {
						?><option value="<?php echo $Level->expRequired; ?>"<?php if ($LevelID == $DummyPlayer->getLevelID()) { ?> selected="selected"<?php } ?>><?php echo $LevelID; ?></option><?php
					} ?>
				</select>
				Ship:
				<select name="ship_type_id"><?php
					foreach ($ShipTypes as $ShipType) {
						?><option value="<?php echo $ShipType->getTypeID(); ?>"<?php if ($ShipType->getTypeID() == $DummyPlayer->getShipTypeID()) { ?> selected="selected"<?php } ?>><?php echo $ShipType->getName(); ?></option><?php
					} ?>
				</select><br /><?php

				foreach ($DummyShip->getWeapons() as $OrderID => $ShipWeapon) { ?>
					Weapon: <?php echo $OrderID + 1; ?>
					<select name="weapons[]">
						<option value="0">None</option><?php
						foreach ($Weapons as $Weapon) {
							?><option value="<?php echo $Weapon->getWeaponTypeID(); ?>"<?php if ($Weapon->getWeaponTypeID() == $ShipWeapon->getWeaponTypeID()) { ?> selected="selected"<?php } ?>><?php echo $Weapon->getName(); ?> (dmg: <?php echo $Weapon->getShieldDamage(); ?>/<?php echo $Weapon->getArmourDamage(); ?> acc: <?php echo $Weapon->getAccuracy(); ?>% lvl:<?php echo $Weapon->getPowerLevel(); ?>)</option><?php
						} ?>
					</select><br /><?php
				}
				for ($OrderID = $DummyShip->getNumWeapons(); $OrderID < $DummyShip->getHardpoints(); $OrderID++) { ?>
					Weapon: <?php echo $OrderID + 1; ?>
					<select name="weapons[]">
						<option value="0">None</option><?php
						foreach ($Weapons as $Weapon) {
							?><option value="<?php echo $Weapon->getWeaponTypeID(); ?>"><?php echo $Weapon->getName(); ?> (dmg: <?php echo $Weapon->getShieldDamage(); ?>/<?php echo $Weapon->getArmourDamage(); ?> acc: <?php echo $Weapon->getAccuracy(); ?>% lvl:<?php echo $Weapon->getPowerLevel(); ?>)</option><?php
						} ?>
					</select><br /><?php
				} ?>
				<input type="submit" name="save_dummy" value="Save Dummy" />
			</form>
		</td>
		<td class="top">
			<span class="underline">Current Details</span>
				<br />Level: <?php echo $DummyPlayer->getLevelID(); ?><br />
				Ship: <?php echo $DummyShip->getName(); ?> (<?php echo $DummyShip->getAttackRating() ?>/<?php echo $DummyShip->getDefenseRating(); ?>)<br />
				DCS: <?php if ($DummyShip->hasDCS()) { ?>Yes<?php } else { ?>No<?php } ?><br />
				Weapons:<br /><?php foreach ($DummyShip->getWeapons() as $ShipWeapon) { ?>* <?php echo $ShipWeapon->getName(); ?><br /><?php } ?>
		</td>
	</tr>
</table>
