<?php

if (count($Planets) > 0) { ?>
	<table id="planet-list" class="standard inset">
		<thead>
			<tr>
				<th class="sort shrink" data-sort="name">Name</th>
				<th class="sort shrink" data-sort="lvl">Level</th>
				<th class="sort shrink" data-sort="owner">Owner</th>
				<th class="sort shrink" data-sort="sector">Sector</th>
				<th class="sort shrink" data-sort="gen">G</th>
				<th class="sort shrink" data-sort="hangar">H</th>
				<th class="sort shrink" data-sort="turret">T</th>
				<th class="sort shrink" data-sort="pShields">Shields</th>
				<th class="sort shrink" data-sort="pCDs">Drones</th>
				<th class="shrink">Supplies</th>
				<th class="sort shrink" data-sort="build">Build</th><?php
				if(isset($IsLeader) && $IsLeader) { ?>
					<th class="bonds shrink">Bonds</th><?php
				} ?>
			</tr>
		</thead>
		<tbody class="list"><?php
			foreach($Planets as &$Planet) { ?>
				<tr id="planet-<?php echo $Planet->getSectorID(); ?>" class="ajax">
					<td class="name"><?php echo $Planet->getName(); ?></td>
					<td class="lvl noWrap"><?php echo number_format($Planet->getLevel(),2); ?></td>
					<td class="owner noWrap"><?php echo $Planet->getOwner()->getLinkedDisplayName(false); ?></td>
					<td class="sector center"><a href="<?php echo Globals::getPlotCourseHREF($ThisPlayer->getSectorID(), $Planet->getSectorID()); ?>"><?php echo $Planet->getSectorID(); ?></a>&nbsp;(<a href="<?php echo $Planet->getGalaxy()->getGalaxyMapHREF(); ?>" target="gal_map"><?php echo $Planet->getGalaxy()->getName(); ?></a>)</td>
					<td class="gen center"><?php echo $Planet->getBuilding(PLANET_GENERATOR); ?></td>
					<td class="hangar center"><?php echo $Planet->getBuilding(PLANET_HANGAR); ?></td>
					<td class="turret center"><?php echo $Planet->getBuilding(PLANET_TURRET); ?></td>
					<td class="pShields center"><?php echo $Planet->getShields(); ?>&nbsp;/&nbsp;<?php echo $Planet->getMaxShields(); ?></td>
					<td class="pCDs center"><?php echo $Planet->getCDs(); ?>&nbsp;/&nbsp;<?php echo $Planet->getMaxCDs(); ?></td>
					<td class="noWrap"><?php
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
					<td class="build noWrap center"><?php
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
					</td><?php
					if(isset($IsLeader) && $IsLeader) { ?>
						<td class="bonds"><?php
							if($Planet->getBonds() > 0) { ?>
								Credits:<br/><?php
								echo $Planet->getBonds(); ?><br/><br/>
								Matures:<br/><?php
								echo format_time($Planet->getMaturity() - TIME, true);
							}
							else {?>
								None<?php
							} ?>
						</td><?php
					} ?>
				</tr><?php
			} ?>
		</tbody>
	</table><br/>
	<script type="text/javascript" src="js/list.1.0.0.min.js"></script>
	<script>
	new List('planet-list', {
		valueNames: ['name', 'lvl', 'owner', 'sector', 'gen', 'hangar', 'turret', 'pShields', 'pCDs', 'build', 'bonds']
	});
	</script><?php
} ?>
