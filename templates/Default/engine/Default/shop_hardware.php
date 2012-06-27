<?php
if (isset($HardwareSold)) { ?>
	<script type="text/javascript">
		function recalcOnKeyUp(hardwareTypeID, cost) {
			var form = document.getElementById('hardwareForm' + hardwareTypeID);
			form.total.value = form.amount.value * cost;
		}
	</script>
	<table class="center standard">
		<tr>
			<th>Name</th>
			<th>Purchase Amount</th>
			<th>&nbsp;</th>
			<th>Unit Cost</th>
			<th>&nbsp;</th>
			<th width="75">Totals</th>
			<th>Action</th>
		</tr><?php
		foreach ($HardwareSold as $HardwareTypeID => $Hardware) {
			$AmountToBuy = $ThisShip->getMaxHardware($HardwareTypeID) - $ThisShip->getHardware($HardwareTypeID); ?>

			<form method="POST" id="hardwareForm<?php echo $HardwareTypeID; ?>" action="<?php echo $Hardware['HREF']; ?>">
				<tr>
					<td><?php echo $Hardware['Name']; ?></td>
					<td><input type="text" name="amount" value="<?php echo $AmountToBuy; ?>" size="5" onKeyUp="recalcOnKeyUp(<?php echo $HardwareTypeID; ?>,<?php echo $Hardware['Cost']; ?>)" id="InputFields" class="center"></td>
					<td>*</td>
					<td><?php echo number_format($Hardware['Cost']); ?></td>
					<td>=</td>
					<td><input type="text" name="total" disabled="disabled" value="<?php echo $AmountToBuy * $Hardware['Cost']; ?>" size="7" id="InputFields" class="center"></td>
					<td>
						<input type="submit" name="action" value="Buy" id="InputFields" />
					</td>
				</tr>
			</form><?php
		} ?>
	</table><?php
}
else {
	?>I have nothing to sell to you. Get out of here!<?php
} ?>