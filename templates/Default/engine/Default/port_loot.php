<table class="center standard">
	<tr>
		<th>Good</th>
		<th>Supply/Demand</th>
		<th>Base Price</th>
		<th>Amount on Ship</th>
		<th>Amount to Trade</th>
		<th>Action</th>
	</tr><?php
	$BoughtGoodIDs = $ThisPort->getVisibleGoodsBought($ThisPlayer);
	foreach ($BoughtGoodIDs as $GoodID) {
		$Good = Globals::getGood($GoodID);
		$Amount = $ThisPort->getGoodAmount($GoodID); ?>
		<form method="POST" action="<?php echo $ThisPort->getLootGoodHREF($GoodID); ?>">
			<tr>
				<td><?php echo $Good['Name']; ?></td>
				<td><?php echo $Amount; ?></td>
				<td><?php echo $Good['BasePrice']; ?></td>
				<td><?php echo $ThisShip->getCargo($Good['ID']); ?></td>
				<td><input type="number" name="amount" value="<?php echo min($Amount, $ThisShip->getCargoHolds()); ?>" size="4" class="InputFields center"></td>
				<td><input type="submit" name="action" value="Loot" class="InputFields" /></td>
			</tr>
		</form><?php
	} ?>
</table>
