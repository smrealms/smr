You are currently building: <?php
	if ($ThisPlanet->hasCurrentlyBuilding()) {
		$CurrentlyBuilding = $ThisPlanet->getCurrentlyBuilding();
		foreach($CurrentlyBuilding as $Building) { ?>
			<br /><?php
			echo $ThisPlanet->getStructureTypes($Building['ConstructionID'])->name(); ?> which will finish in <?php echo format_time($Building['TimeRemaining']); ?>
			<br /><br />
			<div class="buttonA"><a class="buttonA" href="<?php echo $ThisPlanet->getCancelHREF($Building['ConstructionID']); ?>">Cancel</a></div><?php
		}
	}
	else { ?>
		Nothing!<?php
	} ?>

<br /><br />
<table class="standard">
	<tr>
		<th></th>
		<th>Description</th>
		<th>Current</th>
		<th>Cost</th>
		<th>Build</th>
	</tr><?php

	foreach ($ThisPlanet->getStructureTypes() as $StructureID => $Structure) { ?>
		<tr>
			<td><img class="tooltip" id="<?php echo $Structure->name(); ?>_tip" src="images/<?php echo $Structure->image(); ?>" width="16" height="16" alt="" title="<?php echo $Structure->name(); ?>" /></td>
			<td><?php echo $Structure->name(); ?>: <?php echo $Structure->summary(); ?></td>
			<td class="center"><?php echo $ThisPlanet->getBuilding($StructureID); ?>/<?php echo $ThisPlanet->getMaxBuildings($StructureID); ?></td>
			<td><?php
				foreach($Structure->goods() as $GoodID => $Amount) {
					if ($ThisPlanet->getStockpile($GoodID) < $Amount) { ?>
						<span class="red"><?php echo $Amount; ?>-<?php echo $Goods[$GoodID]['Name'];?>, </span><?php
					}
					else {
						echo $Amount; ?>-<?php echo $Goods[$GoodID]['Name']; ?>, <?php
					}
				}

				if ($ThisPlayer->getCredits() < $Structure->creditCost()) { ?>
					<span class="red"><?php echo number_format($Structure->creditCost()); ?>-credits, </span><?php
				} else {
					echo number_format($Structure->creditCost()); ?>-credits, <?php
				}

				foreach ($Structure->hardwareCost() as $hardwareID) {
					if ($hardwareID == HARDWARE_SCANNER) {
						if (!$ThisShip->hasScanner()) { ?>
							<span class="red">1-Scanner, </span><?php
						} else {
							?>1-Scanner, <?php
						}
					}
				}

				echo format_time($ThisPlanet->getConstructionTime($StructureID)); ?>
		</td>
			<td><?php
				if ($ThisPlanet->canBuild($ThisPlayer, $StructureID)===true) { ?>
					<div class="buttonA"><a class="buttonA" href="<?php echo $ThisPlanet->getBuildHREF($StructureID); ?>">Build</a></div><?php
				} else { ?>
					&nbsp;<?php
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
				<li><img src="<?php echo $Goods[$id]['ImageLink']; ?>" title="<?php echo $Goods[$id]['Name']; ?>" alt="<?php echo $Goods[$id]['Name']; ?>" />&nbsp;<?php echo $Goods[$id]['Name']; ?>: <?php echo $Amount; ?></li><?php
			}
		} ?>
	</ul><?php
}
else { ?>
	 Nothing!</p><?php
} ?>
