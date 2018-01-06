<?php

if (count($Planets) > 0) { ?>
	<table id="planet-list" class="standard inset">
		<thead>
			<tr>
				<th class="sort shrink" data-sort="image">
					<a href="<?php echo WIKI_URL; ?>/game-guide/locations#planets" target="_blank"><img align="right" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a></th>
				<th class="sort shrink" data-sort="name">Name</th>
				<th class="sort shrink" data-sort="lvl">Level</th>
				<th class="sort shrink" data-sort="owner">Owner</th>
				<th class="sort shrink" data-sort="sector">Sector</th>
				<th class="sort shrink" data-sort="gen">
					<img class="tooltip" id="generator_tip" src="images/generator.png"  width="16" height="16" alt="G" title="Generators protect a planet with shields. Each generator can hold <?php echo PLANET_GENERATOR_SHIELDS; ?> shields." /></th>
				<th class="sort shrink" data-sort="hangar">
					<img class="tooltip" id="hangar_tip" src="images/hangar.png"  width="16" height="16" alt="H" title="Hangars house and launch combat drones(CDs). Each hangar holds <?php echo PLANET_HANGAR_DRONES; ?> drones." /></th>
				<th class="sort shrink" data-sort="bunker">
					<img class="tooltip" id="bunker_tip" src="images/bunker.png"  width="16" height="16" alt="B" title="Bunkers fortify the defensive structures with additional armour.  Each bunker holds <?php echo PLANET_BUNKER_ARMOUR; ?> units of armour." /></th>
				<th class="sort shrink" data-sort="turret">
					<img class="tooltip" id="turret_tip" src="images/turret.png"  width="16" height="16" alt="T" title="Turrets fire heavy laser beams. Each planet can have a maximum of 10 turrets. These laser beams do 250/250 damage. When they fire at an attacking ship, they can destroy 250 shields, or 250 armor (but not both on the same shot)." /></th>
				<th class="sort shrink" data-sort="pShields">Shields</th>
				<th class="sort shrink" data-sort="pCDs">Drones</th>
				<th class="sort shrink" data-sort="pArmour">Armour</th>
				<th class="shrink">Supplies</th>
				<th class="sort shrink" data-sort="build">Build</th>
			</tr>
		</thead>
		<tbody class="list"><?php
			foreach($Planets as &$Planet) { ?>
				<tr id="planet-<?php echo $Planet->getSectorID(); ?>" class="ajax">
					<td class="image noWrap">
						<img class="tooltip" id="planet_image" src="<?php echo $Planet->getTypeImage(); ?>"  width="16" height="16" alt="" title="<?php echo $Planet->getTypename().': '.$Planet->getTypeDescription(); ?>" /></td>
					<td class="name"><?php echo $Planet->getName(); ?></td>
					<td class="lvl center"><?php echo number_format($Planet->getLevel(),2); ?></td>
					<td class="owner noWrap"><?php echo $Planet->getOwner()->getLinkedDisplayName(false); ?></td>
					<td class="sector center"><a href="<?php echo Globals::getPlotCourseHREF($ThisPlayer->getSectorID(), $Planet->getSectorID()); ?>"><?php echo $Planet->getSectorID(); ?></a>&nbsp;(<a href="<?php echo $Planet->getGalaxy()->getGalaxyMapHREF(); ?>" target="gal_map"><?php echo $Planet->getGalaxy()->getName(); ?></a>)</td>
					<td class="gen center"><?php echo $Planet->getBuilding(PLANET_GENERATOR); ?></td>
					<td class="hangar center"><?php echo $Planet->getBuilding(PLANET_HANGAR); ?></td>
					<td class="bunker center"><?php echo $Planet->getBuilding(PLANET_BUNKER); ?></td>
					<td class="turret center"><?php echo $Planet->getBuilding(PLANET_TURRET); ?></td>
					<td class="pShields center"><?php echo $Planet->getShields(); ?>&nbsp;/&nbsp;<?php echo $Planet->getMaxShields(); ?></td>
					<td class="pCDs center"><?php echo $Planet->getCDs(); ?>&nbsp;/&nbsp;<?php echo $Planet->getMaxCDs(); ?></td>
					<td class="pArmour center"><?php echo $Planet->getArmour(); ?>&nbsp;/&nbsp;<?php echo $Planet->getMaxArmour(); ?></td>
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
					</td>
				</tr><?php
			} ?>
		</tbody>
	</table><br/>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
	<script>
	var list = new List('planet-list', {
		valueNames: ['name', 'lvl', 'owner', 'sector', 'gen', 'hangar', 'bunker', 'turret', 'pShields', 'pCDs', 'pArmour', 'build', 'bonds'],
		sortFunction: function(a, b, options) {
			return list.utils.naturalSort(a.values()[options.valueName].replace(/<.*?>|,/g,''), b.values()[options.valueName].replace(/<.*?>|,/g,''), options);
		}
	});
	</script><?php
} ?>
