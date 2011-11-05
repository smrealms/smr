<div align="center">
	<?php
	if (count($AlliancePlanets) > 0)
	{ ?>
		Your alliance currently has <?php echo $AlliancePlanets; ?> planets in the universe!<br /><br />
		<table class="standard inset">
			<tr>
				<th>Name</th>
				<th>Owner</th>
				<th>Sector</th>
				<th>G</th>
				<th>H</th>
				<th>T</th>
				<th>Shields</th>
				<th>Drones</th>
				<th>Supplies</th>
				<th>Build</th>
			</tr><?php
			foreach($AlliancePlanets as &$AlliancePlanet)
			{ ?>
				<tr>
					<td><?php echo $AlliancePlanet->getName(); ?></td>
					<td class="shrink noWrap"><?php echo $AlliancePlanet->getOwner()->getLinkedDisplayName(false); ?></td>
					<td class="shrink center"><?php echo $AlliancePlanet->getSectorID(); ?></td>
					<td class="shrink center"><?php echo $AlliancePlanet->getBuilding(1); ?></td>
					<td class="shrink center"><?php echo $AlliancePlanet->getBuilding(2); ?></td>
					<td class="shrink center"><?php echo $AlliancePlanet->getBuilding(3); ?></td>
					<td class="shrink center"><?php echo $AlliancePlanet->getShields(); ?></td>
					<td class="shrink center"><?php echo $AlliancePlanet->getCDs(); ?></td>
					<td class="shrink center"><?php echo $AlliancePlanet->getName(); ?></td>
					<td class="shrink noWrap"><?php
						$Supply = false;
						foreach ($AlliancePlanet->getStockpile() as $GoodID => $Amount)
						{
							if ($Amount > 0)
							{
								$Supply = true;
								$Good = Globals::getGood($GoodID); ?>
								<img src="<?php echo $Good['ImageLink']; ?>" title="<?php echo $Good['Name']; ?>" alt="<?php echo $Good['Name']; ?>" />&nbsp;<?php echo $Amount; ?><br /><?php
							}
						}
						if ($Supply === false)
						{
							?>none<?php
						} ?>
					</td>
					<td class="shrink noWrap center"><?
						if ($AlliancePlanet->hasCurrentlyBuilding())
						{
							$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
							foreach($AlliancePlanet->getCurrentlyBuilding() as $Building)
							{
								echo $PLANET_BUILDINGS[$Building['ConstructionID']]['Name']; ?><br /><?php
								echo format_time($building['TimeRemaining'], true);
							}
						}
						else
						{
							?>Nothing<?php
						} ?>
					</td>
				</tr><?php
			} ?>
		</table><?php
	}
	else
	{ ?>
		Your alliance has no claimed planets<?php
	} ?>
</div>