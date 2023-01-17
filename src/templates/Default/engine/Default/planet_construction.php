<?php declare(strict_types=1);

/**
 * @var Smr\Planet $ThisPlanet
 * @var Smr\Player $ThisPlayer
 * @var Smr\Ship $ThisShip
 * @var array<int, Smr\TradeGood> $Goods
 */

?>
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
		<th width="8%">Action</th>
	</tr><?php

	foreach ($ThisPlanet->getStructureTypes() as $StructureID => $Structure) { ?>
		<tr>
			<td><img src="images/<?php echo $Structure->image(); ?>" width="16" height="16" alt="" title="<?php echo $Structure->name(); ?>" /></td>
			<td class="left"><?php echo $Structure->name(); ?>: <?php echo $Structure->summary(); ?></td>
			<td><?php echo $ThisPlanet->getBuilding($StructureID); ?> / <?php echo $ThisPlanet->getMaxBuildings($StructureID); ?></td>
			<td class="left noWrap"><?php
				foreach ($Structure->goods() as $GoodID => $Amount) {
					$Good = $Goods[$GoodID]; ?>
					&nbsp;<?php echo $Good->getImageHTML(); ?>&nbsp;<span <?php if ($ThisPlanet->getStockpile($GoodID) < $Amount) { ?> class="red" <?php } ?>><?php echo $Amount; ?></span>&nbsp;<br /><?php
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
				if ($ThisPlanet->getBuildRestriction($ThisPlayer, $StructureID) === false) { ?>
					<div class="buttonA"><a class="buttonA" href="<?php echo $ThisPlanet->getBuildHREF($StructureID); ?>">Build (<?php echo TURNS_TO_BUILD; ?>)</a></div><?php
				} ?>
			</td>
		</tr><?php
	} ?>

</table>

<p>Your stockpile contains:<?php
if ($ThisPlanet->hasStockpile()) { ?>
	</p>
	<ul class="nobullets"><?php
		foreach ($ThisPlanet->getStockpile() as $GoodID => $Amount) {
			if ($Amount > 0) {
				$Good = $Goods[$GoodID]; ?>
				<li class="pad1"><?php echo $Good->getImageHTML(); ?>&nbsp;<?php echo $Good->name; ?>: <?php echo $Amount; ?></li><?php
			}
		} ?>
	</ul><?php
} else { ?>
	 Nothing!</p><?php
}
