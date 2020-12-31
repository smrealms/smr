<?php
if ($ThisShip->hasWeapons()) { ?>
	<div class="center">
		<p>To reorder your weapons simply drag and drop them into the desired order.</p>
		<noscript><p>It has been detected that you do not have javascript or that it is disabled, you will have to use the arrows to reorder your weapons</p></noscript>
		
		<form method="POST" action="<?php echo Globals::getWeaponReorderHREF(0, 'Form'); ?>">
			<table id="weapon_reorder" class="standard center">
			<tr nodrag="true" nodrop="true">
			<th>Weapon Name</th>
			<th>Shield<br />Damage</th>
			<th>Armour<br />Damage</th>
			<th>Accuracy</th>
			<th>Power<br />Level</th>
			<th>Action</th>
			</tr><?php
			foreach ($ThisShip->getWeapons() as $OrderID => $Weapon) { ?>
				<tr>
					<td class="left"><?php echo $Weapon->getName() ?></td>
					<td><?php echo $Weapon->getShieldDamage() ?></td>
					<td><?php echo $Weapon->getArmourDamage() ?></td>
					<td><?php echo $Weapon->getBaseAccuracy() ?>%</td>
					<td><?php echo $Weapon->getPowerLevel() ?></td>
					<td>
						<input type="hidden" name="weapon_reorder[]" value="<?php echo $OrderID ?>" />
						<a href="<?php echo Globals::getWeaponReorderHREF($OrderID, 'Up') ?>" onclick="this.href='javascript:void(0)';">
							<img onclick="moveRow(this.parentNode.parentNode, -1)" src="images/up.gif" width="24" height="24" alt="" title="Move up">
						</a>
						<a href="<?php echo Globals::getWeaponReorderHREF($OrderID, 'Down') ?>" onclick="this.href='javascript:void(0)';">
							<img onclick="moveRow(this.parentNode.parentNode, 1)" src="images/down.gif" width="24" height="24" alt="" title="Move down">
						</a>
					</td>
				</tr><?php
			} ?>
			</table>
			<br />
			<input type="submit" value="Update Weapon Order" />
		</form>
	</div>
	<?php $this->addJavascriptSource('js/weapon_reorder.js');
} else {
	?>You don't have any weapons!<?php
} ?>
