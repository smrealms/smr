<?php
if (isset($HardwareSold)) { ?>
	<table class="standard">
		<tr>
			<th align="center">Name</th>
			<th align="center">Purchase Amount</th>
			<th>&nbsp;</th>
			<th align="center">Unit Cost</th>
			<th>&nbsp;</th>
			<th align="center" width="75">Totals</th>
			<th align="center">Action</th>
		</tr><?php
		foreach ($HardwareSold as $HardwareTypeID => $Hardware) {
			$AmountToBuy = $ThisShip->getMaxHardware($HardwareTypeID) - $ThisShip->getHardware($HardwareTypeID); ?>

			<script type="text/javascript">
				function recalc_<?php echo $HardwareTypeID; ?>_onkeyup() {
					var form = document.getElementById('hardwareForm<?php echo $HardwareTypeID; ?>');
					form.total.value = form.amount.value * <?php echo $Hardware['Cost']; ?>;
				}
			</script>
			<form method="POST" id="hardwareForm<?php echo $HardwareTypeID; ?>" action="<?php echo $Hardware['HREF']; ?>">
				<tr>
					<td align="center"><?php echo $Hardware['Name']; ?></td>
					<td align="center"><input type="text" name="amount" value="<?php echo $AmountToBuy; ?>" size="5" onKeyUp="recalc_<?php echo $HardwareTypeID; ?>_onkeyup()" id="InputFields" class="center"></td>
					<td>*</td>
					<td align="center"><?php echo number_format($Hardware['Cost']); ?></td>
					<td>=</td>
					<td align="center"><input type="text" name="total" value="<?php echo $AmountToBuy * $Hardware['Cost']; ?>" size="7" id="InputFields" class="center"></td>
					<td align="center">
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