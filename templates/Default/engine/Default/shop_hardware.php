<?php
if (isset($HardwareSold)) { ?>
	<script type="text/javascript">
		function recalcOnKeyUp(transaction, hardwareTypeID, cost) {
			var form = document.getElementById(transaction + hardwareTypeID);
			form.total.value = form.amount.value * cost;
		}
	</script>
	<h2>Buy Hardware</h2>
	<table class="standard">
		<tr>
			<th>Name</th>
			<th>Purchase Amount</th>
			<th width="80">Unit Cost</th>
			<th>Total Cost</th>
			<th>Action</th>
		</tr><?php
		foreach ($HardwareSold as $HardwareTypeID => $Hardware) {
			$AmountToBuy = $ThisShip->getMaxHardware($HardwareTypeID) - $ThisShip->getHardware($HardwareTypeID); ?>

			<form method="POST" id="buy<?php echo $HardwareTypeID; ?>" action="<?php echo $Hardware['HREF']; ?>">
				<tr>
					<td><?php echo $Hardware['Name']; ?></td>
					<td><input type="number" name="amount" value="<?php echo $AmountToBuy; ?>" size="5" onKeyUp="recalcOnKeyUp('buy',<?php echo $HardwareTypeID; ?>,<?php echo $Hardware['Cost']; ?>)" id="InputFields" class="center"></td>
					<td class="center"><?php echo number_format($Hardware['Cost']); ?></td>
					<td><input type="number" name="total" disabled="disabled" value="<?php echo $AmountToBuy * $Hardware['Cost']; ?>" size="7" id="InputFields" class="center"></td>
					<td class="center">
						<input type="submit" name="action" value="Buy" id="InputFields" />
					</td>
				</tr>
			</form><?php
		} ?>
	</table><?php

	if (isset($HardwareSold[HARDWARE_COMBAT])) { ?>
		<br />
		<h2>Sell Hardware</h2>
		<table class="standard">
			<tr>
				<th>Name</th>
				<th>Sell Amount</th>
				<th width="80">Unit Value</th>
				<th>Total Refund</th>
				<th>Action</th>
			</tr><?php
			$Hardware = $HardwareSold[HARDWARE_COMBAT];
			$UnitRefund = round($Hardware['Cost'] * CDS_REFUND_PERCENT);
			$MaxAmountToSell = $ThisShip->getHardware(HARDWARE_COMBAT); ?>
			<form method="POST" id="sell<?php echo HARDWARE_COMBAT; ?>" action="<?php echo $Hardware['HREF']; ?>">
				<tr>
					<td><?php echo $Hardware['Name']; ?></td>
					<td><input type="number" name="amount" value="<?php echo $MaxAmountToSell; ?>" size="5" onKeyUp="recalcOnKeyUp('sell',<?php echo HARDWARE_COMBAT; ?>,<?php echo $UnitRefund; ?>)" class="center"></td>
					<td class="center"><?php echo number_format($UnitRefund); ?></td>
					<td><input type="number" name="total" disabled="disabled" value="<?php echo $MaxAmountToSell * $UnitRefund; ?>" size="7" class="center"></td>
					<td class="center"><input type="submit" name="action" value="Sell" /></td>
				</tr>
			</form>
		</table><?php
	}
}
else {
	?>I have nothing to sell to you. Get out of here!<?php
} ?>
