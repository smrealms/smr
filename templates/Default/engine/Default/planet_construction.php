You are currently building: <?php
	if ($ThisPlanet->hasCurrentlyBuilding()) {
		$CurrentlyBuilding = $ThisPlanet->getCurrentlyBuilding();
		foreach ($CurrentlyBuilding as $Building) { ?>
			<br /><?php
			echo $ThisPlanet->getStructureTypes($Building['ConstructionID'])->name(); ?> which will finish in <?php echo format_time($Building['TimeRemaining']); ?>
			<br /><br />
			<div class="buttonA"><a class="buttonA" href="<?php echo $ThisPlanet->getCancelHREF($Building['ConstructionID']); ?>">Cancel</a></div><?php
		}
	} else { ?>
		Nothing!<?php
	} ?>

<br /><br />
<table class="standard center">
	<tr>
		<th class="shrink"></th>
		<th>Description</th>
		<th>Capacity</th>
		<th colspan="2">Cost</th>
		<th width="8%">Build</th>
	</tr><?php

	foreach ($ThisPlanet->getStructureTypes() as $StructureID => $Structure) { ?>
		<tr>
			<td><img src="images/<?php echo $Structure->image(); ?>" width="16" height="16" alt="" title="<?php echo $Structure->name(); ?>" /></td>
			<td class="left"><?php echo $Structure->name(); ?>: <?php echo $Structure->summary(); ?></td>
			<td><?php echo $ThisPlanet->getBuilding($StructureID); ?> / <?php echo $ThisPlanet->getMaxBuildings($StructureID); ?></td>
			<td class="left noWrap"><?php
				foreach ($Structure->goods() as $GoodID => $Amount) {
					$Good = $Goods[$GoodID]; ?>
					&nbsp;<img class="bottom" src="<?php echo $Good['ImageLink']; ?>" width="13" height="16" title="<?php echo $Good['Name']; ?>" alt="" />&nbsp;<span <?php if ($ThisPlanet->getStockpile($GoodID) < $Amount) { ?> class="red" <?php } ?>><?php echo $Amount; ?></span>&nbsp;<br /><?php
				} ?>
			</td>
			<td class="noWrap">
				<span <?php if ($ThisPlayer->getCredits() < $Structure->creditCost()) { ?> class="red" <?php } ?>><?php echo number_format($Structure->creditCost()); ?> credits</span><?php

				foreach ($Structure->hardwareCost() as $hardwareID) {
					if ($hardwareID == HARDWARE_SCANNER) { ?>
						<br /><span <?php if (!$ThisShip->hasScanner()) { ?> class="red" <?php } ?>>1 Scanner</span><?php
					}
				} ?>
				<br /><?php echo format_time($ThisPlanet->getConstructionTime($StructureID), true); ?>
		</td>
			<td><?php
				if ($ThisPlanet->canBuild($ThisPlayer, $StructureID) === true) { ?>
					<div class="buttonA"><a class="buttonA" href="<?php echo $ThisPlanet->getBuildHREF($StructureID); ?>">Build</a></div><?php
				} ?>
			</td>
		</tr><?php
	} ?>

</table>

<p>Your stockpile contains:<?php
if ($ThisPlanet->hasStockpile()) { ?>
	</p>
	<ul><?php
		foreach ($ThisPlanet->getStockpile() as $id => $Amount) {
			if ($Amount > 0) { ?>
				<li><img src="<?php echo $Goods[$id]['ImageLink']; ?>" width="13" height="16" title="<?php echo $Goods[$id]['Name']; ?>" alt="<?php echo $Goods[$id]['Name']; ?>" />&nbsp;<?php echo $Goods[$id]['Name']; ?>: <?php echo $Amount; ?></li><?php
			}
		} ?>
	</ul><?php
} else { ?>
	 Nothing!</p><?php
} ?>
