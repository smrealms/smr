<?php

if (count($ShipsSold) > 0) { ?>
	<table class="standard">
		<tr>
			<th>Name</th>
			<th>Cost</th>
			<th>Action</th>
		</tr><?php
		foreach ($ShipsSold as $ShipSold) { ?>
			<tr>
				<td><?php echo $ShipSold['Name']; ?></td>
				<td class="center"><?php echo number_format($ShipSold['Cost']); ?></td>
				<td>
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $ShipsSoldHREF[$ShipSold['ShipTypeID']]; ?>">View Details</a>
					</div>
				</td>
			</tr><?php
		}
	?></table><?php
} else {
	?>We've got nothing for you here! Get outta here!<br /><?php
}
?><br /><?php
if (isset($CompareShip)) { ?>
	<table class="standard">
		<tr>
			<th>&nbsp;</th>
			<th><?php echo $ThisShip->getName(); ?></th>
			<th><?php echo $CompareShip['Name']; ?></th>
			<th>Change</th>
		</tr><?php
		foreach (Globals::getHardwareTypes() as $HardwareTypeID => $Hardware) { ?>
			<tr class="center">
				<td class="left"><?php echo $Hardware['Name']; ?></td>
				<td><?php echo $ThisShip->getMaxHardware($HardwareTypeID); ?></td>
				<td><?php echo $CompareShip['MaxHardware'][$HardwareTypeID]; ?></td>
				<td><?php echo number_colour_format($CompareShip['MaxHardware'][$HardwareTypeID] - $ThisShip->getMaxHardware($HardwareTypeID)); ?></td>
			</tr><?php
		} ?>
		<tr class="center">
			<td class="left">Hardpoints</td>
			<td><?php echo $ThisShip->getHardpoints(); ?></td>
			<td><?php echo $CompareShip['Hardpoint']; ?></td>
			<td><?php echo number_colour_format($CompareShip['Hardpoint'] - $ThisShip->getHardpoints()); ?></td>
		</tr>
		<tr class="center">
			<td class="left">Speed</td>
			<td><?php echo $ThisShip->getRealSpeed(); ?></td>
			<td><?php echo $CompareShip['RealSpeed']; ?></td>
			<td><?php echo number_colour_format($CompareShip['RealSpeed'] - $ThisShip->getRealSpeed()); ?></td>
		</tr>
		<tr class="center">
			<td class="left">Turns</td>
			<td><?php echo $ThisPlayer->getTurns() ?></td>
			<td><?php echo $CompareShip['Turns']; ?></td>
			<td><?php echo number_colour_format($CompareShip['Turns'] - $ThisPlayer->getTurns()); ?></td>
		</tr>
	</table><br />

	<table class="nobord">
		<tr>
			<td colspan="2"><hr style="width:200px"></td>
		</tr>
		<tr>
			<td class="right"><?php echo $CompareShip['Name']; ?> Cost</td>
			<td class="right red"><?php echo number_format($CompareShip['Cost']); ?></td>
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
					<a class="buttonA" href="<?php echo $CompareShip['BuyHREF']; ?>">Buy</a>
				</div>
			</td>
		</tr>
	</table><?php
} ?>
