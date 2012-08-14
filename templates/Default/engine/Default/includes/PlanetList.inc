<?php

if (count($Planets) > 0) { ?>
	<table class="standard inset">
		<tr>
			<th>Name</th>
			<th>Level</th>
			<th>Owner</th>
			<th>Sector</th>
			<th>G</th>
			<th>H</th>
			<th>T</th>
			<th>Shields</th>
			<th>Drones</th>
			<th>Supplies</th>
			<th>Build</th>
			<?php if(isset($isLeader) && $isLeader) { echo '<th>Bonds</th>';}?>
		</tr><?php
		foreach($Planets as &$Planet) { ?>
			<tr>
				<td><?php echo $Planet->getName(); ?></td>
				<td class="shrink noWrap"><?php echo number_format($Planet->getLevel(),2); ?></td>
				<td class="shrink noWrap"><?php echo $Planet->getOwner()->getLinkedDisplayName(false); ?></td>
				<td class="shrink center"><a href="<?php echo Globals::getPlotCourseHREF($ThisPlayer->getSectorID(), $Planet->getSectorID()); ?>"><?php echo $Planet->getSectorID(); ?></a>&nbsp;(<a href="<?php echo $Planet->getGalaxy()->getGalaxyMapHREF(); ?>" target="gal_map"><?php echo $Planet->getGalaxy()->getName(); ?></a>)</td>
				<td class="shrink center"><?php echo $Planet->getBuilding(PLANET_GENERATOR); ?></td>
				<td class="shrink center"><?php echo $Planet->getBuilding(PLANET_HANGAR); ?></td>
				<td class="shrink center"><?php echo $Planet->getBuilding(PLANET_TURRET); ?></td>
				<td class="shrink center"><?php echo $Planet->getShields(); ?></td>
				<td class="shrink center"><?php echo $Planet->getCDs(); ?></td>
				<td class="shrink noWrap"><?php
					$Supply = false;
					foreach ($Planet->getStockpile() as $GoodID => $Amount) {
						if ($Amount > 0) {
							$Supply = true;
							$Good = Globals::getGood($GoodID); ?>
							<img src="<?php echo $Good['ImageLink']; ?>" title="<?php echo $Good['Name']; ?>" alt="<?php echo $Good['Name']; ?>" />&nbsp;<?php echo $Amount; ?><br /><?php
						}
					}
					if ($Supply === false) {
						?>none<?php
					} ?>
				</td>
				<td class="shrink noWrap center"><?php
					if ($Planet->hasCurrentlyBuilding()) {
						$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
						foreach($Planet->getCurrentlyBuilding() as $Building) {
							echo $PLANET_BUILDINGS[$Building['ConstructionID']]['Name']; ?><br /><?php
							echo format_time($Building['TimeRemaining'], true);
						}
					}
					else {
						?>Nothing<?php
					} ?>
				</td>
				<?php
				if(isset($isLeader) && $isLeader) {
					echo '<td>';
					if($Planet->getBonds() > 0) {
						echo '<p>Credits:<br>', ($Planet->getBonds()), '</p><p>Matures:<br>', date(DATE_FULL_SHORT_SPLIT,$Planet->getMaturity()), '</p>';
					}
					else {
						echo 'None';
					}
					echo '</td>';
				}
				?>
			</tr><?php
		} ?>
	</table><br><?php
} ?>