<table class="center standard">
	<tr>
		<th>Good</th>
		<th>Supply/Demand</th>
		<th>Base Price</th>
		<th>Amount on Ship</th>
		<th>Amount to Trade</th>
		<th>Action</th>
	</tr><?php
	$BoughtGoods =& $ThisPort->getVisibleGoodsBought($ThisPlayer);
	foreach($BoughtGoods as &$Good) { ?>
		<form method="POST" action="<?php echo $ThisPort->getLootGoodHREF($Good); ?>">
			<tr>
				<td><?php echo $Good['Name']; ?></td>
				<td><?php echo $Good['Amount']; ?></td>
				<td><?php echo $Good['BasePrice']; ?></td>
				<td><?php echo $ThisShip->getCargo($Good['ID']); ?></td>
				<td><input type="text" name="amount" value="<?php echo min($Good['Amount'], $ThisShip->getCargoHolds()); ?>" size="4" id="InputFields" class="center"></td>
				<td><input type="submit" name="action" value="Loot" id="InputFields" /></td>
			</tr>
		</form><?php
	} unset($Good); ?>
</table>