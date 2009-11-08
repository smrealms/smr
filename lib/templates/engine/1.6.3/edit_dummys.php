<a href="<?php echo $CombatSimLink ?>">Combat Simulator</a><br /><br />

<form action="<?php echo $EditDummysLink ?>" method="POST">
	Edit Dummy:
	<select name="dummy_name"><?php
		foreach($DummyNames as $DummyName)
		{
			?><option value="<?php echo $DummyName ?>"<?php if($DummyName==$DummyPlayer->getPlayerName()){ ?> selected="selected"<?php } ?>><?php echo $DummyName ?></option><?php
		} ?>
	</select><br />
	<input type="submit" value="Select Dummy" />
</form>
<?php $DummyShip =& $DummyPlayer->getShip() ?>
<table>
	<tr>
		<td style="vertical-align:top">
			<u><?php echo $DummyPlayer->getPlayerName() ?></u><br /><br />
			<form action="<?php echo $EditDummysLink ?>" method="POST">
				<input type="text" name="dummy_name" value="<?php echo $DummyPlayer->getPlayerName() ?>" />
				Level
				<select name="level">
					<?php foreach($Levels as $Level)
					{
						?><option value="<?php echo $Level['Requirement'] ?>"<?php if($Level['ID']==$DummyPlayer->getLevelID()){ ?> selected="selected"<?php } ?>><?php echo $Level['ID'] ?></option><?php
					} ?>
				</select>
				Ship:
				<select name="ship_id"><?php
				foreach($BaseShips as $BaseShip)
					{
						?><option value="<?php echo $BaseShip['ShipTypeID'] ?>"<?php if($BaseShip['ShipTypeID']==$DummyPlayer->getShipTypeID()){ ?> selected="selected"<?php } ?>><?php echo $BaseShip['Name'] ?></option><?php
					} ?>
				</select><br />
				
				<?php
				foreach($DummyShip->getWeapons() as $OrderID => $ShipWeapon)
				{ ?>
					Weapon: <?php echo $OrderID ?>
					<select name="weapons[]"><?php
						foreach($Weapons as $Weapon)
						{
							?><option value="<?php echo $Weapon->getWeaponTypeID() ?>"<?php if($Weapon->getWeaponTypeID()==$ShipWeapon->getWeaponTypeID()){ ?> selected="selected"<?php } ?>><?php echo $Weapon->getName() ?> (dmg: <?php echo $Weapon->getShieldDamage() ?>/<?php echo $Weapon->getArmourDamage() ?> acc: <?php echo $Weapon->getBaseAccuracy() ?>% lvl:<?php echo $Weapon->getPowerLevel() ?>)</option><?php
						} ?>
					</select><br />
				<?php } ?>
				<input type="submit" name="save_dummy" value="Save Dummy" />
			</form>
		</td>
		<td style="vertical-align:top">
			<u>Current Details</u>
				<br />Level: <?php echo $DummyPlayer->getLevelID() ?><br />
				Ship: <?php echo $DummyShip->getName() ?> (<?php echo $DummyShip->getAttackRating() ?>/<?php echo $DummyShip->getDefenseRating() ?>)<br />
				DCS: <?php if($DummyShip->hasDCS()){ ?>Yes<?php }else{ ?>No<?php } ?><br />
				Weapons: <?php foreach($DummyShip->getWeapons() as $ShipWeapon){ ?>* <?php echo $ShipWeapon->getName() ?><br /><?php } ?>
		</td>
	</tr>
</table>