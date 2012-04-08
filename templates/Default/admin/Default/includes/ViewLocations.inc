<?php
foreach($Locations as $Location) { ?>
<tr>
	<td><?php echo $Location->getName() ?></td>
	<td><?php echo $Location->getAction() ?></td>
	<td><?php echo $Location->getImage() ?></td>
	<td><?php echo $Location->isFed() ?></td>
	<td><?php echo $Location->isBar() ?></td>
	<td><?php echo $Location->isBank() ?></td>
	<td><?php echo $Location->isHQ() ?></td>
	<td><?php echo $Location->isUG() ?></td>
	<td><?php
		foreach($Location->getHardwareSold() as $Hardware) {
			echo $Hardware ?><br /><?php
		} ?>
	</td>
	<td><?php
		foreach($Location->getShipsSold() as $Ship) {
			echo $Ship['Name'] ?><br /><?php
		} ?>
	</td>
	<td>
		<?php foreach($Location->getWeaponsSold() as $Weapon) {
			echo $Weapon->getName() ?><br /><?php
		} ?>
	</td>
	<td>
		<div class="buttonA">
			<a href="<?php echo $Location->getEditHREF() ?>" class="buttonA"> Edit </a>
		</div>
	</td>
</tr><?php
} ?>