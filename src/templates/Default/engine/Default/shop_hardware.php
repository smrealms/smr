<?php declare(strict_types=1);

/**
 * @var Smr\Ship $ThisShip
 * @var ?array<int, array{HREF: string, Cost: int, Name: string}> $HardwareSold
 */

if (isset($HardwareSold)) { ?>
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
			$AmountToBuy = $ThisShip->getType()->getMaxHardware($HardwareTypeID) - $ThisShip->getHardware($HardwareTypeID); ?>

			<tr>
				<td><?php echo $Hardware['Name']; ?></td>
				<td><input form="buy<?php echo $HardwareTypeID; ?>" type="number" name="amount" value="<?php echo $AmountToBuy; ?>" size="5" onKeyUp="recalcOnKeyUp('buy',<?php echo $HardwareTypeID; ?>,<?php echo $Hardware['Cost']; ?>)" class="center"></td>
				<td class="center"><?php echo number_format($Hardware['Cost']); ?></td>
				<td><input form="buy<?php echo $HardwareTypeID; ?>" type="number" name="total" disabled="disabled" value="<?php echo $AmountToBuy * $Hardware['Cost']; ?>" size="7" class="center"></td>
				<td class="center">
					<form method="POST" id="buy<?php echo $HardwareTypeID; ?>" action="<?php echo $Hardware['HREF']; ?>">
						<input type="submit" name="action" value="Buy" />
					</form>
				</td>
			</tr><?php
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
			<tr>
				<td><?php echo $Hardware['Name']; ?></td>
				<td><input form="sell<?php echo HARDWARE_COMBAT; ?>" type="number" name="amount" value="<?php echo $MaxAmountToSell; ?>" size="5" onKeyUp="recalcOnKeyUp('sell',<?php echo HARDWARE_COMBAT; ?>,<?php echo $UnitRefund; ?>)" class="center"></td>
				<td class="center"><?php echo number_format($UnitRefund); ?></td>
				<td><input form="sell<?php echo HARDWARE_COMBAT; ?>" type="number" name="total" disabled="disabled" value="<?php echo $MaxAmountToSell * $UnitRefund; ?>" size="7" class="center"></td>
				<td class="center">
					<form method="POST" id="sell<?php echo HARDWARE_COMBAT; ?>" action="<?php echo $Hardware['HREF']; ?>">
						<input type="submit" name="action" value="Sell" />
					</form>
				</td>
			</tr>
		</table><?php
	}
} else {
	?>I have nothing to sell to you. Get out of here!<?php
}
