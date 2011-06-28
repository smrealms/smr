<?php

if (count($ShipsSold) > 0 )
{ ?>
	<table class="standard">
		<tr>
			<th>Name</th>
			<th>Cost</th>
			<th>Action</th>
		</tr><?php
		foreach($ShipsSold as &$ShipSold)
		{ ?>
			<tr>
				<td><?php echo $ShipSold['Name']; ?></td>
				<td><?php echo $ShipSold['Cost']; ?></td>
				<td>
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $ShipsSoldHREF[$ShipSold['ShipTypeID']];?>">&nbsp;View Details&nbsp;</a>
					</div>
				</td>
			</tr><?php
		} unset($ShipSold);
	?></table><?php
}
else
{
	?>We've got nothing for you here! Get outta here!<br /><?php
}
?><br /><?php
if ($CompareShip)
{ ?>
	<table class="standard">
		<tr>
			<th>&nbsp;</th>
			<th><?php echo $ThisShip->getName(); ?></th>
			<th><?php echo $CompareShip['Name']; ?></th>
			<th>Change</th>
		</tr>
		<tr>
			<td>Shields</td>
			<td><?php echo $ThisShip->getMaxShields(); ?></td>
			<td><?php echo $CompareShip['MaxHardware'][HARDWARE_SHIELDS]; ?></td>
			<td><?php echo number_colour_format($CompareShip['MaxHardware'][HARDWARE_SHIELDS]-$ThisShip->getMaxShields()); ?></td>
		</tr>
		<tr>
			<td>Armour</td>
			<td><?php echo $ThisShip->getMaxArmour(); ?></td>
			<td><?php echo $CompareShip['MaxHardware'][HARDWARE_ARMOUR]; ?></td>
			<td><?php echo number_colour_format($CompareShip['MaxHardware'][HARDWARE_ARMOUR]-$ThisShip->getMaxArmour()); ?></td>
		</tr>
		<tr>
			<td>Combat Drones</td>
			<td><?php echo $ThisShip->getMaxCDs(); ?></td>
			<td><?php echo $CompareShip['MaxHardware'][HARDWARE_COMBAT]; ?></td>
			<td><?php echo number_colour_format($CompareShip['MaxHardware'][HARDWARE_COMBAT]-$ThisShip->getMaxCDs()); ?></td>
		</tr>
		<tr>
			<td>Scout Drones</td>
			<td><?php echo $ThisShip->getMaxSDs(); ?></td>
			<td><?php echo $CompareShip['MaxHardware'][HARDWARE_SCOUT]; ?></td>
			<td><?php echo number_colour_format($CompareShip['MaxHardware'][HARDWARE_SCOUT]-$ThisShip->getMaxSDs()); ?></td>
		</tr>
		<tr>
			<td>Mines</td>
			<td><?php echo $ThisShip->getMaxMines(); ?></td>
			<td><?php echo $CompareShip['MaxHardware'][HARDWARE_MINE]; ?></td>
			<td><?php echo number_colour_format($CompareShip['MaxHardware'][HARDWARE_MINE]-$ThisShip->getMaxMines()); ?></td>
		</tr>
		<tr>
			<td>Cargo Holds</td>
			<td><?php echo $ThisShip->getMaxCargoHolds(); ?></td>
			<td><?php echo $CompareShip['MaxHardware'][HARDWARE_CARGO]; ?></td>
			<td><?php echo number_colour_format($CompareShip['MaxHardware'][HARDWARE_CARGO]-$ThisShip->getMaxCargoHolds()); ?></td>
		</tr>
		<tr>
			<td>Hardpoints</td>
			<td><?php echo $ThisShip->getHardpoints(); ?></td>
			<td><?php echo $CompareShip['Hardpoint']; ?></td>
			<td><?php echo number_colour_format($CompareShip['Hardpoint']-$ThisShip->getHardpoints()); ?></td>
		</tr>
		<tr>
			<td>Speed</td>
			<td><?php echo $ThisShip->getRealSpeed(); ?></td>
			<td><?php echo $CompareShip['Speed']; ?></td>
			<td><?php echo number_colour_format($CompareShip['Speed']-$ThisShip->getRealSpeed()); ?></td>
		</tr>
		<tr>
			<td>Scanner</td>
			<td><?php if($ThisShip->canHaveScanner()) { ?>+<?php } else { ?>-<?php } ?></td>
			<td><?php if($CompareShip['MaxHardware'][HARDWARE_SCANNER]) { ?>+<?php } else { ?>-<?php } ?></td>
			<td><?php echo number_colour_format($CompareShip['MaxHardware'][HARDWARE_SCANNER]-$ThisShip->canHaveScanner(),true); ?></td>
		</tr>
		<tr>
			<td>Illusion</td>
			<td><?php if($ThisShip->canHaveIllusion()) { ?>+<?php } else { ?>-<?php } ?></td>
			<td><?php if($CompareShip['MaxHardware'][HARDWARE_ILLUSION]) { ?>+<?php } else { ?>-<?php } ?></td>
			<td><?php echo number_colour_format($CompareShip['MaxHardware'][HARDWARE_ILLUSION]-$ThisShip->canHaveIllusion(),true); ?></td>
		</tr>
		<tr>
			<td>Jump</td>
			<td><?php if($ThisShip->canHaveJump()) { ?>+<?php } else { ?>-<?php } ?></td>
			<td><?php if($CompareShip['MaxHardware'][HARDWARE_JUMP]) { ?>+<?php } else { ?>-<?php } ?></td>
			<td><?php echo number_colour_format($CompareShip['MaxHardware'][HARDWARE_JUMP]-$ThisShip->canHaveJump(),true); ?></td>
		</tr>
		<tr>
			<td>Cloak</td>
			<td><?php if($ThisShip->canHaveCloak()) { ?>+<?php } else { ?>-<?php } ?></td>
			<td><?php if($CompareShip['MaxHardware'][HARDWARE_CLOAK]) { ?>+<?php } else { ?>-<?php } ?></td>
			<td><?php echo number_colour_format($CompareShip['MaxHardware'][HARDWARE_CLOAK]-$ThisShip->canHaveCloak(),true); ?></td>
		</tr>
		<tr>
			<td>DCS</td>
			<td><?php if($ThisShip->canHaveDCS()) { ?>+<?php } else { ?>-<?php } ?></td>
			<td><?php if($CompareShip['MaxHardware'][HARDWARE_DCS]) { ?>+<?php } else { ?>-<?php } ?></td>
			<td><?php echo number_colour_format($CompareShip['MaxHardware'][HARDWARE_DCS]-$ThisShip->canHaveDCS(),true); ?></td>
		</tr>
	</table><br />

	<table cellspacing="0"class="nobord">
		<tr>
			<td colspan="2"><hr style="width:200px"></td>
		</tr>
		<tr>
			<td class="right">Ship Cost</td><td class="right"><?php echo number_colour_format($CompareShip['Cost']); ?></td>
		</tr>
		<tr>
			<td colspan="2"><hr style="width:200px"></td>
		</tr>
		<tr>
			<td class="right">Ship Refund</td><td class="right"><?php echo number_colour_format(-($ThisShip->getCost() >> 1)); ?></td>
		</tr>
		<tr>
			<td colspan="2"><hr style="width:200px"></td>
		</tr>
		<tr>
			<td class="right">Total Cost</td><td class="right"><?php echo number_colour_format($CompareShip['Cost'] - ($ThisShip->getCost() >> 1)); ?></td>
		</tr>
		<tr>
			<td colspan="2"><hr style="width:200px"></td>
		</tr>
		<tr>
			<td class="right" colspan="2">
				<div class="buttonA">
					<a class="buttonA" href="<?php echo $CompareShip['BuyHREF'];?>">&nbsp;Buy&nbsp;</a>
				</div>
			</td>
		</tr>
	</table><?php
}
?>