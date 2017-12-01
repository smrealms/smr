You are currently building: <?php
	if ($ThisPlanet->hasCurrentlyBuilding()) {
		$CurrentlyBuilding = $ThisPlanet->getCurrentlyBuilding();
		foreach($CurrentlyBuilding as $Building) { ?>
			<br /><?php
			echo $PlanetBuildings[$Building['ConstructionID']]['Name']; ?> which will finish in <?php echo format_time($Building['TimeRemaining']); ?>
			<br /><br />
			<div class="buttonA"><a class="buttonA" href="<?php echo $ThisPlanet->getCancelHREF($Building); ?>">&nbsp;Cancel&nbsp;</a></div><?php
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

	foreach($PlanetBuildings as $PlanetBuilding) {
		if ($ThisPlanet->getMaxBuildings($PlanetBuilding['ConstructionID']) > 0) { ?>
			<tr>
				<td><img class="tooltip" id="<?php echo $PlanetBuilding['Name']; ?>_tip" src="<?php echo $PlanetBuilding['Image']; ?>" width="16" height="16" alt="" title="<?php echo $PlanetBuilding['Name']; ?>"/></td>
				<td><?php echo $PlanetBuilding['Name'];?>: <?php echo $PlanetBuilding['Description']; ?></td>
				<td class="center"><?php echo $ThisPlanet->getBuilding($PlanetBuilding['ConstructionID']); ?>/<?php echo $ThisPlanet->getMaxBuildings($PlanetBuilding['ConstructionID']); ?></td>
				<td><?php
					foreach($PlanetBuilding['Goods'] as $GoodID => $Amount) {
						if ($ThisPlanet->getStockpile($GoodID) < $Amount) { ?>
							<span class="red"><?php echo $Amount; ?>-<?php echo $Goods[$GoodID]['Name'];?>, </span><?php
						}
						else {
							echo $Amount; ?>-<?php echo $Goods[$GoodID]['Name']; ?>,<?php
						}
					}

					if ($ThisPlayer->getCredits() < $PlanetBuilding['Credit Cost'][$ThisPlanet->getTypeID()]) { ?>
						<span class="red"><?php echo number_format($PlanetBuilding['Credit Cost'][$ThisPlanet->getTypeID()]); ?>-credits, </span><?php
					}
					else {
						 echo number_format($PlanetBuilding['Credit Cost'][$ThisPlanet->getTypeID()]); ?>-credits, <?php
					}

					echo format_time($ThisPlanet->getConstructionTime($PlanetBuilding['ConstructionID'])); ?>
				</td>
				<td><?php
					if ($ThisPlanet->canBuild($ThisPlayer, $PlanetBuilding['ConstructionID'])===true) { ?>
						<div class="buttonA"><a class="buttonA" href="<?php echo $ThisPlanet->getBuildHREF($PlanetBuilding); ?>">&nbsp;Build&nbsp;</a></div><?php
					}
					else { ?>
						&nbsp;<?php
					} ?>
				</td>
			</tr><?php
		}
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
