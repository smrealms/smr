<?php

if (count($Planets) > 0) { ?>
	<table id="planet-list" class="standard inset left centered">
		<thead>
			<tr>
				<th class="sort shrink" data-sort="image">
					<a href="<?php echo WIKI_URL; ?>/game-guide/locations#planets" target="_blank"><img class="bottom" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a></th>
				<th class="sort" data-sort="name">Name</th>
				<th class="sort shrink" data-sort="lvl">Level</th>
				<th class="sort" data-sort="owner">Owner</th>
				<th class="sort shrink" data-sort="sector">Sector</th>
				<th class="shrink">Structures</th>
				<th class="shrink">Hardware</th>
				<th class="shrink">Supplies</th>
				<th class="sort" data-sort="build">Build</th>
			</tr>
		</thead>
		<tbody class="list"><?php
			foreach ($Planets as $Planet) { ?>
				<tr id="planet-<?php echo $Planet->getSectorID(); ?>" class="ajax">
					<td class="image noWrap">
						<img src="<?php echo $Planet->getTypeImage(); ?>"  width="16" height="16" alt="" title="<?php echo $Planet->getTypename() . ': ' . $Planet->getTypeDescription(); ?>" /></td>
					<td class="name"><?php echo $Planet->getDisplayName(); ?></td>
					<td class="lvl center"><?php echo number_format($Planet->getLevel(), 2); ?></td>
					<td class="owner noWrap"><?php echo $Planet->getOwner()->getLinkedDisplayName(false); ?></td>
					<td class="sector center"><a href="<?php echo Globals::getPlotCourseHREF($ThisPlayer->getSectorID(), $Planet->getSectorID()); ?>"><?php echo $Planet->getSectorID(); ?></a>&nbsp;(<a href="<?php echo $Planet->getGalaxy()->getGalaxyMapHREF(); ?>" target="gal_map"><?php echo $Planet->getGalaxy()->getName(); ?></a>)</td>
					<td class="noWrap"><?php
						foreach ($Planet->getStructureTypes() as $Structure) { ?>
							<img style="padding:1px;" class="bottom" src="images/<?php echo $Structure->image(); ?>"  width="16" height="16" alt="" title="<?php echo $Structure->name(); ?>" />&nbsp;<?php echo $Planet->getBuilding($Structure->structureID()); ?>&nbsp;/&nbsp;<?php echo $Planet->getMaxBuildings($Structure->structureID()); ?><br /><?php
						} ?>
					</td>
					<td class="noWrap"><?php
						if ($Planet->hasStructureType(PLANET_GENERATOR)) { ?>
							<img style="padding:1px" class="bottom" src="images/shields.png" width="16" height="16" alt="" title="Shields" />&nbsp;<?php echo $Planet->getShields(); ?>&nbsp;/&nbsp;<?php echo $Planet->getMaxShields(); ?><br /><?php
						}
						if ($Planet->hasStructureType(PLANET_HANGAR)) { ?>
							<img style="padding:1px" class="bottom" src="images/cd.png" width="16" height="16" alt="" title="Combat Drones" />&nbsp;<?php echo $Planet->getCDs(); ?>&nbsp;/&nbsp;<?php echo $Planet->getMaxCDs(); ?><br /><?php
						}
						if ($Planet->hasStructureType(PLANET_BUNKER)) { ?>
							<img style="padding:1px" class="bottom" src="images/armour.png" width="16" height="16" alt="" title="Armour" />&nbsp;<?php echo $Planet->getArmour(); ?>&nbsp;/&nbsp;<?php echo $Planet->getMaxArmour(); ?><br /><?php
						}
						if ($Planet->hasStructureType(PLANET_WEAPON_MOUNT)) { ?>
							<img style="padding:1px" class="bottom" src="images/weapon_shop.png" width="16" height="16" alt="" title="Mounted Weapons" />&nbsp;<?php echo count($Planet->getMountedWeapons()); ?>&nbsp;/&nbsp;<?php echo $Planet->getBuilding(PLANET_WEAPON_MOUNT);
						}
					?></td>
					<td class="noWrap"><?php
						$Supply = false;
						foreach ($Planet->getStockpile() as $GoodID => $Amount) {
							if ($Amount > 0) {
								$Supply = true;
								$Good = Globals::getGood($GoodID); ?>
								&nbsp;<img style="padding:1px" class="bottom" src="<?php echo $Good['ImageLink']; ?>" width="13" height="16" title="<?php echo $Good['Name']; ?>" alt="<?php echo $Good['Name']; ?>" />&nbsp;<?php echo $Amount; ?><br /><?php
							}
						}
						if ($Supply === false) {
							?>None<?php
						} ?>
					</td>
					<td class="build noWrap center"><?php
						if ($Planet->hasCurrentlyBuilding()) {
							foreach ($Planet->getCurrentlyBuilding() as $Building) {
								echo $Planet->getStructureTypes($Building['ConstructionID'])->name(); ?><br /><?php
								echo format_time($Building['TimeRemaining'], true);
							}
						} else {
							?>Nothing<?php
						} ?>
					</td>
				</tr><?php
			} ?>
		</tbody>
	</table><br />
	<?php $this->setListjsInclude('PlanetList');
} ?>
