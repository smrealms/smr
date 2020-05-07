<?php
if (isset($ErrorMsg)) {
	echo $ErrorMsg; ?><br /><?php
}
if (isset($Msg)) {
	echo $Msg; ?><br /><?php
}
?>


<table class="standardnobord fullwidth">

	<tr>
		<td>
			<img class="bottom" src="<?php echo $ThisPlanet->getTypeImage()?>" width="16" height="16" alt="Planet" title="<?php echo $ThisPlanet->getTypeName(); ?>" /> 
			&nbsp;<b><?php echo $ThisPlanet->getTypeName() ?>:</b> <?php echo $ThisPlanet->getTypeDescription(); ?>
			<a href="<?php echo WIKI_URL; ?>/game-guide/locations#planets" target="_blank"><img style="float: right;" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a>
		</td>
	<tr>
		<td style="width:50%">
			<table class="standard">
				<tr>
					<th width="145">&nbsp;</th>
					<th width="75">Current</th>
					<th width="75">Max</th>
				</tr>
				
				<tr>
					<td>Planet Level</td>
					<td class="center"><span id="planetLevel"><?php echo number_format($ThisPlanet->getLevel(), 2); ?></span></td>
					<td class="center"><?php echo number_format($ThisPlanet->getMaxLevel(), 2); ?></td>
				</tr>
			</table>
			<br />
			<table class="standard">
				<tr>
					<th width="145">Defensive <br>Structures</th>
					<th width="75">Current</th>
					<th width="75">Max</th>
				</tr><?php
				foreach ($ThisPlanet->getStructureTypes() as $StructureID => $Structure) { ?>
				<tr>
					<td title="<?php echo $Structure->tooltip(); ?>">
						<img src="images/<?php echo $Structure->image(); ?>" width="16" height="16" alt="" />&nbsp;<?php echo $Structure->name(); ?>
					</td>
					<td class="center"><span id="planetStructure<?php echo $StructureID; ?>"><?php echo $ThisPlanet->getBuilding($StructureID); ?></span></td>
					<td class="center"><?php echo $ThisPlanet->getMaxBuildings($StructureID); ?></td>
				</tr><?php
				} ?>
			</table>
			<br />
			<?php if ($ThisPlanet->getBuildings()) { ?>
			<table class="standard">
				<tr>
					<th width="145">Installed <br>Hardware</th>
					<th width="75">Amount</th>
					<th width="75">Accuracy</th>
				</tr>
				<?php if ($ThisPlanet->hasBuilding(PLANET_GENERATOR)) { ?>
				<tr>
					<td><img src="images/shields.png"  width="16" height="16" alt="" title="Shields"/>&nbsp;Shields</td>
					<td class="center"><span id="planetShields"><?php echo $ThisPlanet->getShields(); ?></span> / <span id="planetMaxShields"><?php echo $ThisPlanet->getMaxShields(); ?></span></td>
					<td>&nbsp;</td>
				</tr>
				<?php } if ($ThisPlanet->hasBuilding(PLANET_HANGAR)) { ?>
				<tr>
					<td><img src="images/cd.png"  width="16" height="16" alt="" title="Combat Drones"/>&nbsp;Combat Drones</td>
					<td class="center"><span id="planetCDs"><?php echo $ThisPlanet->getCDs(); ?></span> / <span id="planetMaxCDs"><?php echo $ThisPlanet->getMaxCDs(); ?></span></td>
					<td class="center">100 %</td>
				</tr>
				<?php } if ($ThisPlanet->hasBuilding(PLANET_BUNKER)) { ?>
				<tr>
					<td><img src="images/armour.png"  width="16" height="16" alt="" title="Armour"/>&nbsp;Armour</td>
					<td class="center"><span id="planetArmour"><?php echo $ThisPlanet->getArmour(); ?></span> / <span id="planetMaxArmour"><?php echo $ThisPlanet->getMaxArmour(); ?></span></td>
					<td class="center">&nbsp;</td>
				</tr>
				<?php }	if ($ThisPlanet->hasBuilding(PLANET_TURRET)) { ?>
				<tr>
					<td><img src="images/turrets.png"  width="16" height="16" alt="" title="Turret"/>&nbsp;Turrets</td>
					<td class="center"><span id="planetTurrets2"><?php echo $ThisPlanet->getBuilding(PLANET_TURRET); ?></span> / <?php echo $ThisPlanet->getMaxBuildings(PLANET_TURRET); ?></td>
					<td class="center"><span id="planetAcc"><?php echo number_format($ThisPlanet->accuracy(), 2); ?></span> %</td>
				</tr>
				<?php }	if ($ThisPlanet->hasBuilding(PLANET_WEAPON_MOUNT)) { ?>
				<tr>
					<td><img src="images/weapon_shop.png"  width="16" height="16" alt="" title="Weapon"/>&nbsp;Mounted Weapons</td>
					<td class="center"><span id="planetWeapons"><?php echo count($ThisPlanet->getMountedWeapons()); ?></span> / <?php echo $ThisPlanet->getBuilding(PLANET_WEAPON_MOUNT); ?></td>
					<td class="center">+<?php echo $ThisPlanet->getAccuracyBonus(); ?>%</td>
				</tr>
				<?php } ?>
				</table>
			<?php } ?>
		</td><?php
		if (isset($Ticker)) { ?>
			<td><?php
				$this->includeTemplate('includes/Ticker.inc'); ?>
			</td><?php
		} ?>
	</tr>
</table>
<p><a href="<?php echo $LaunchLink; ?>" class="submitStyle">Launch</a></p>
<?php
$this->includeTemplate('includes/SectorPlayers.inc', array('PlayersContainer'=>$ThisPlanet));
?>
