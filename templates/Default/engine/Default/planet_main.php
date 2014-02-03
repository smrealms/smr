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
			<img align="left" src="<?php echo $ThisPlanet->getTypeImage()?>" width="16" height="16" alt="Planet" title="<?php echo $ThisPlanet->getTypeName(); ?>" /> 
			&nbsp;<b><?php echo $ThisPlanet->getTypeName() ?>:</b> <?php echo $ThisPlanet->getTypeDescription(); ?>
		</td>
	<tr>
		<td style="width:50%">
			<a href="http://wiki.smrealms.de/index.php?title=Planets" target="_blank"><img align="right" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a>
			<table class="standard">
				<tr>
					<th width="145">&nbsp;</th>
					<th width="75">Current</th>
					<th width="75">Max</th>
				</tr>
				
				<tr>
					<td>Planet Level</td>
					<td align="center"><span id="planetLevel"><?php echo number_format($ThisPlanet->getLevel(),2); ?></span></td>
					<td align="center"><?php echo number_format($ThisPlanet->getMaxLevel(),2); ?></td>
				</tr>
			</table>
			<br />
			<table class="standard">
				<tr>
					<th width="145">Defensive <br>Structures</th>
					<th width="75">Current</th>
					<th width="75">Max</th>
				</tr>
				<?php if ($ThisPlanet->getMaxBuildings(PLANET_GENERATOR) > 0) { ?>
				<tr>
					<td><img class="tooltip" id="generator_tip" src="images/generator.png"  width="16" height="16" alt="Information" title="Generators protect a planet with shields. Each generator can hold <?php echo PLANET_GENERATOR_SHIELDS; ?> shields." /> 
						<label for="generator_tip">&nbsp;Generator</td>
					<td align="center"><span id="planetGens"><?php echo $ThisPlanet->getBuilding(PLANET_GENERATOR); ?></span></td>
					<td align="center"><?php echo $ThisPlanet->getMaxBuildings(PLANET_GENERATOR); ?></td>
				</tr>
				<?php } if ($ThisPlanet->getMaxBuildings(PLANET_HANGAR) > 0) { ?>
				<tr>
					<td><img class="tooltip" id="hangar_tip" src="images/hangar.png" width="16" height="16" alt="Information" title="Hangars house and launch combat drones(CDs). Each hangar holds <?php echo PLANET_HANGAR_DRONES; ?> drones."/> 
						<label for="hangar_tip">&nbsp;Hangar</label></td>
					<td align="center"><span id="planetHangars"><?php echo $ThisPlanet->getBuilding(PLANET_HANGAR); ?></span></td>
					<td align="center"><?php echo $ThisPlanet->getMaxBuildings(PLANET_HANGAR); ?></td>
				</tr>
				<?php } if ($ThisPlanet->getMaxBuildings(PLANET_BUNKER) > 0) { ?>
				<tr>
					<td><img class="tooltip" id="bunker_tip" src="images/bunker.png" width="16" height="16" alt="Information" title="Bunkers fortify the defensive structures with additional armour.  Each bunker holds <?php echo PLANET_BUNKER_ARMOUR; ?> units of armour."/> 
						<label for="bunker_tip">&nbsp;Bunker</td>
					<td align="center"><span id="planetHangars"><?php echo $ThisPlanet->getBuilding(PLANET_BUNKER); ?></span></td>
					<td align="center"><?php echo $ThisPlanet->getMaxBuildings(PLANET_BUNKER); ?></td>
				</tr>
				<?php } if ($ThisPlanet->getMaxBuildings(PLANET_TURRET) > 0) { ?>
				<tr>
					<td><img class="tooltip" id="turret_tip" src="images/turret.png"  width="16" height="16" alt="Information" title="Turrets fire heavy laser beams. Each planet can have a maximum of 10 turrets. These laser beams do 250/250 damage. When they fire at an attacking ship, they can destroy 250 shields, or 250 armor (but not both on the same shot)."/> 
						<label for="turret_tip">&nbsp;Turret</label></td>
					<td align="center"><span id="planetTurrets1"><?php echo $ThisPlanet->getBuilding(PLANET_TURRET); ?></span></td>
					<td align="center"><?php echo $ThisPlanet->getMaxBuildings(PLANET_TURRET); ?></td>
				</tr>
				<?php }	?>
			</table>
			<br />
			<?php if ($ThisPlanet->getBuilding(PLANET_GENERATOR) 
				+ $ThisPlanet->getBuilding(PLANET_HANGAR) 
				+ $ThisPlanet->getBuilding(PLANET_BUNKER)
				+ $ThisPlanet->getBuilding(PLANET_TURRET) > 0) { ?>
			<table class="standard">
				<tr>
					<th width="145">Installed <br>Hardware</th>
					<th width="75">Amount</th>
					<th width="75">Accuracy</th>
				</tr>
				<?php if ($ThisPlanet->getBuilding(PLANET_GENERATOR) > 0) { ?>
				<tr>
					<td><img id="shields" src="images/shields.png"  width="16" height="16" alt="" title="Shields"/>&nbsp;Shields</td>
					<td align="center"><span id="planetShields"><?php echo $ThisPlanet->getShields(); ?></span> / <span id="planetMaxShields"><?php echo $ThisPlanet->getMaxShields(); ?></span></td>
					<td>&nbsp;</td>
				</tr>
				<?php } if ($ThisPlanet->getBuilding(PLANET_HANGAR) > 0) { ?>
				<tr>
					<td><img id="cds" src="images/cd.png"  width="16" height="16" alt="" title="Combat Drones"/>&nbsp;Combat Drones</td>
					<td align="center"><span id="planetCDs"><?php echo $ThisPlanet->getCDs(); ?></span> / <span id="planetMaxCDs"><?php $ThisPlanet->getMaxCDs(); ?></span></td>
					<td align="center">100 %</td>
				</tr>
				<?php } if ($ThisPlanet->getBuilding(PLANET_BUNKER) > 0) { ?>
				<tr>
					<td><img id="turret" src="images/armour.png"  width="16" height="16" alt="" title="Armour"/>&nbsp;Armour</td>
					<td align="center"><span id="planetArmour"><?php echo $ThisPlanet->getArmour(); ?></span> / <span id="planetMaxArmour"><?php $ThisPlanet->getMaxArmour(); ?></span></td>
					<td align="center">&nbsp;</td>
				</tr>
				<?php }	if ($ThisPlanet->getBuilding(PLANET_TURRET) > 0) { ?>
				<tr>
					<td><img id="turret" src="images/turrets.png"  width="16" height="16" alt="" title="Turret"/>&nbsp;Turrets</td>
					<td align="center"><span id="planetTurrets2"><?php echo $ThisPlanet->getBuilding(PLANET_TURRET); ?></span> / <?php echo $ThisPlanet->getMaxBuildings(PLANET_TURRET); ?></td>
					<td align="center"><span id="planetAcc"><?php echo number_format($ThisPlanet->accuracy(), 2); ?></span> %</td>
				</tr>
				<?php } ?>
				</table>
			<?php } ?>
		</td><?php
		if(isset($Ticker)) { ?>
			<td><?php
				$this->includeTemplate('includes/Ticker.inc'); ?>
			</td><?php
		} ?>
	</tr>
</table>
<br />
<form name="LaunchForm" method="POST" action="<?php echo $LaunchFormLink; ?>">
	<input type="submit" name="action" value="Launch" id="InputFields"/>
</form>
<br /><?php
$this->includeTemplate('includes/SectorPlayers.inc',array('PlayersContainer'=>&$ThisPlanet));
?>