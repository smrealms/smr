<?php declare(strict_types=1);

if (count($ShipsSold) > 0) { ?>
	<h2>Available Ships</h2>
	<table class="standard">
		<tr>
			<th>Name</th>
			<th>Cost</th>
			<th>Action</th>
		</tr><?php
		foreach ($ShipsSold as $ShipTypeID => $ShipType) { ?>
			<tr>
				<td><?php echo $ShipType->getName(); ?></td>
				<td class="center"><?php echo number_format($ShipType->getCost()); ?></td>
				<td>
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $ShipsSoldHREF[$ShipTypeID]; ?>">View Details</a>
					</div>
				</td>
			</tr><?php
		}
	?></table><?php
	if ($ShipsUnavailable) { ?>
		<br />
		<h2>Under Construction</h2>
		<table class="standard">
			<tr>
				<th>Name</th>
				<th>Time To Completion</th>
			</tr><?php
			foreach ($ShipsUnavailable as $Ship) { ?>
				<tr>
					<td><?php echo $Ship['Name']; ?></td>
					<td><?php echo format_time($Ship['TimeUntilUnlock']); ?></td>
				</tr><?php
			} ?>
		</table><?php
	}
} else {
	?>We've got nothing for you here! Get outta here!<br /><?php
}
?><br /><?php
if (isset($CompareShip)) { ?>
	<h2>Details</h2>
	<table class="standard">
		<tr>
			<th>&nbsp;</th>
			<th><?php echo $ThisShip->getName(); ?></th>
			<th><?php echo $CompareShip->getName(); ?></th>
			<th>Change</th>
		</tr><?php
		foreach ($ShipDiffs as $Label => $Diff) { ?>
			<tr class="center">
				<td class="left"><?php echo $Label; ?></td>
				<td><?php echo $Diff['Old']; ?></td>
				<td><?php echo $Diff['New']; ?></td>
				<td><?php echo number_colour_format($Diff['New'] - $Diff['Old']); ?></td>
			</tr><?php
		} ?>
	</table><br />

	<table class="nobord">
		<tr>
			<td colspan="2"><hr style="width:200px"></td>
		</tr>
		<tr>
			<td class="right"><?php echo $CompareShip->getName(); ?> Cost</td>
			<td class="right red"><?php echo number_format($CompareShip->getCost()); ?></td>
		</tr>
		<tr>
			<td class="right"><?php echo $ThisShip->getName(); ?> Trade-In</td>
			<td class="right green">- <?php echo number_format($TradeInValue); ?></td>
		</tr>
		<tr>
			<td colspan="2"><hr style="width:200px"></td>
		</tr>
		<tr><?php
			if ($TotalCost >= 0) { ?>
				<td class="right">Total Cost</td>
				<td class="right red"><?php echo number_format($TotalCost); ?></td><?php
			} else { ?>
				<td class="right">Total Refund</td>
				<td class="right green"><?php echo number_format(-$TotalCost); ?></td><?php
			} ?>
		</tr>
		<tr>
			<td colspan="2"><hr style="width:200px"></td>
		</tr>
		<tr>
			<td class="right" colspan="2">
				<div class="buttonA">
					<a class="buttonA" href="<?php echo $BuyHREF; ?>">Buy</a>
				</div>
			</td>
		</tr>
	</table><?php
} ?>
