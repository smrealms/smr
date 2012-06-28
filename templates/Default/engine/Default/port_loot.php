<div align="center">
	<table class="standard">
		<tr>
			<th align="">Good</th>
			<th align="center">Supply/Demand</th>
			<th align="center">Base Price</th>
			<th align="center">Amount on Ship</th>
			<th align="center">Amount to Trade</th>
			<th align="center">Action</th>
		</tr><?php
		$BoughtGoods =& $ThisPort->getVisibleGoodsBought($ThisPlayer);
		foreach($BoughtGoods as &$Good) { ?>
			<form method="POST" action="<?php echo $ThisPort->getLootGoodHREF($Good); ?>">
				<tr>
					<td align="center"><?php echo $Good['Name']; ?></td>
					<td align="center"><?php echo $Good['Amount']; ?></td>
					<td align="center"><?php echo $Good['BasePrice']; ?></td>
					<td align="center"><?php echo $ThisShip->getCargo($Good['ID']); ?></td>
					<td align="center"><input type="text" name="amount" value="<?php echo min($Good['Amount'], $ThisShip->getCargoHolds()); ?>" size="4" id="InputFields" class="center"></td>
					<td align="center"><input type="submit" name="action" value="Loot" id="InputFields" /></td>
				</tr>
			</form><?php
		} unset($Good); ?>
	</table>
</div>