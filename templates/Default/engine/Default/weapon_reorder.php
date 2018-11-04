<?php
if($ThisShip->hasWeapons()) { ?>
	<div align="center">
		<p>To reorder your weapons simply drag and drop them into the desired order.</p>
		<noscript><p>It has been detected that you do not have javascript or that it is disabled, you will have to use the arrows to reorder your weapons</p></noscript>
		
		<form method="POST" action="<?php echo Globals::getWeaponReorderHREF(0,'Form'); ?>">
			<table id="weapon_reorder" class="standard center">
			<tr nodrag="true" nodrop="true">
			<th>Weapon Name</th>
			<th>Shield<br />Damage</th>
			<th>Armour<br />Damage</th>
			<th>Accuracy</th>
			<th>Power<br />Level</th>
			<th>Action</th>
			</tr><?php
			foreach($ThisShip->getWeapons() as $OrderID => $Weapon) { ?>
				<tr>
					<td class="left"><?php echo $Weapon->getName() ?></td>
					<td><?php echo $Weapon->getShieldDamage() ?></td>
					<td><?php echo $Weapon->getArmourDamage() ?></td>
					<td><?php echo $Weapon->getBaseAccuracy() ?>%</td>
					<td><?php echo $Weapon->getPowerLevel() ?></td>
					<td><input type="hidden" name="weapon_reorder[]" value="<?php echo $OrderID ?>" />
						<noscript><a href="<?php echo Globals::getWeaponReorderHREF($OrderID,'Up') ?>"></noscript><?php
						if($OrderID > 0) { ?>
							<img style="cursor:pointer;" onclick="moveRow(this.parentNode,-1)" src="images/up.gif" width="24" height="24" alt="Switch up" title="Switch up"><?php
						}
						else { ?>
							<img style="cursor:pointer;" onclick="moveRow(this.parentNode,-1)" src="images/up_push.gif" width="24" height="24" alt="Push up" title="Push up"><?php
						} ?>
						<noscript></a>
						<a href="<?php echo Globals::getWeaponReorderHREF($OrderID,'Down') ?>"></noscript><?php
						if($OrderID < $ThisShip->getNumWeapons()-1) { ?>
							<img style="cursor:pointer;" onclick="moveRow(this.parentNode,1)" src="images/down.gif" width="24" height="24" alt="Switch down" title="Switch down"><?php
						}
						else { ?>
							<img style="cursor:pointer;" onclick="moveRow(this.parentNode,1)" src="images/down_push.gif" width="24" height="24" alt="Push down" title="Push down"><?php
						} ?>
						<noscript></a></noscript>
					</td>
				</tr><?php
			} ?>
			</table>
			<br />
			<input type="submit" value="Update Weapon Order" />
		</form>
	</div>
	<script src="js/weapon_reorder.js"></script><?php
}
else {
	?>You don't have any weapons!<?php
} ?>
