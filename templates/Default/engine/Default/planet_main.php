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
			<img align="left" src="<?php echo $ThisPlanet->getTypeImage()?>" width="16" height="16" alt="Planet" title="<?php echo $ThisPlanet->getTypeName()?>" /> 
			&nbsp;<b><?php echo $ThisPlanet->getTypeName() ?>:</b> <?php echo $ThisPlanet->getTypeDescription() ?>
		</td>
	<tr>
		<td style="width:50%">
			<a href="http://wiki.smrealms.de/index.php?title=Planets" target="_blank"><img align="right" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a>
			<table class="standard">
				<tr>
					<th width="125">&nbsp;</th>
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
					<th width="125">Defensive Structures</th>
					<th width="75">Current</th>
					<th width="75">Max</th>
				</tr>
				<?php 
					if ($ThisPlanet->getMaxBuildings(PLANET_GENERATOR) > 0) {
						echo '<tr>
							<td><img class="tooltip" src="images/silk/information.png"  width="16" height="16" alt="Information" title="Generators protect a planet with shields. Each generator can hold '.PLANET_GENERATOR_SHIELDS.' shields." /> Generator</td>
							<td align="center"><span id="planetGens">'.$ThisPlanet->getBuilding(PLANET_GENERATOR).'</span></td>
							<td align="center">'.$ThisPlanet->getMaxBuildings(PLANET_GENERATOR).'</td>
							</tr>';
					}
					if ($ThisPlanet->getMaxBuildings(PLANET_HANGAR) > 0) {
						echo '<tr>
							<td><img class="tooltip" src="images/silk/information.png" width="16" height="16" alt="Information" title="Hangars house and launch combat drones(CDs). Each hangar holds '.PLANET_HANGAR_DRONES.' drones."/> Hangar</td>
							<td align="center"><span id="planetHangars">'.$ThisPlanet->getBuilding(PLANET_HANGAR).'</span></td>
							<td align="center">'.$ThisPlanet->getMaxBuildings(PLANET_HANGAR).'</td>
							</tr>';
					}
					if ($ThisPlanet->getMaxBuildings(PLANET_BUNKER) > 0) {
						echo '<tr>
							<td><img class="tooltip" src="images/silk/information.png" width="16" height="16" alt="Information" title="Bunkers fortify the defensive structures with additional armour.  Each bunker holds '.PLANET_BUNKER_ARMOUR.' units of armour."/> Bunker</td>
							<td align="center"><span id="planetHangars">'.$ThisPlanet->getBuilding(PLANET_BUNKER).'</span></td>
							<td align="center">'.$ThisPlanet->getMaxBuildings(PLANET_BUNKER).'</td>
							</tr>';
					}
					if ($ThisPlanet->getMaxBuildings(PLANET_TURRET) > 0) {
						echo '<tr>
							<td><img class="tooltip" src="images/silk/information.png"  width="16" height="16" alt="Information" title="Turrets fire heavy laser beams. Each planet can have a maximum of 10 turrets. These laser beams do 250/250 damage. When they fire at an attacking ship, they can destroy 250 shields, or 250 armor (but not both on the same shot)."/> Turret</td>
							<td align="center"><span id="planetTurrets1">'.$ThisPlanet->getBuilding(PLANET_TURRET).'</span></td>
							<td align="center">'.$ThisPlanet->getMaxBuildings(PLANET_TURRET).'</td>
							</tr>';
					}
				?>
			</table>
			<br />
			<?php if ($ThisPlanet->getBuilding(PLANET_GENERATOR) 
				+ $ThisPlanet->getBuilding(PLANET_HANGAR) 
				+ $ThisPlanet->getBuilding(PLANET_BUNKER)
				+ $ThisPlanet->getBuilding(PLANET_TURRET) > 0) {
				echo '<table class="standard">
				<tr>
					<th width="125">Installed Hardware</th>
					<th width="75">Amount</th>
					<th width="75">Accuracy</th>
				</tr>';
				if ($ThisPlanet->getBuilding(PLANET_GENERATOR) > 0) {
					echo '<tr>
						<td>Shields</td>
						<td align="center"><span id="planetShields">'.$ThisPlanet->getShields().'</span> / <span id="planetMaxShields">'.$ThisPlanet->getMaxShields().'</span></td>
						<td>&nbsp;</td>
						</tr>';
				}
				if ($ThisPlanet->getBuilding(PLANET_HANGAR) > 0) {
					echo '<tr>
						<td>Combat Drones</td>
						<td align="center"><span id="planetCDs">'.$ThisPlanet->getCDs().'</span> / <span id="planetMaxCDs">'.$ThisPlanet->getMaxCDs().'</span></td>
						<td align="center">100 %</td>
						</tr>';
				}
				if ($ThisPlanet->getBuilding(PLANET_BUNKER) > 0) {
					echo '<tr>
						<td>Armour</td>
						<td align="center"><span id="planetArmour">'.$ThisPlanet->getArmour().'</span> / <span id="planetMaxArmour">'.$ThisPlanet->getMaxArmour().'</span></td>
						<td align="center">&nbsp;</td>
						</tr>';
				}
				if ($ThisPlanet->getBuilding(PLANET_TURRET) > 0) {		
					echo '<tr>
						<td>Turrets</td>
						<td align="center"><span id="planetTurrets2">'.$ThisPlanet->getBuilding(PLANET_TURRET).'</span> / '.$ThisPlanet->getMaxBuildings(PLANET_TURRET).'</td>
						<td align="center"><span id="planetAcc">'.number_format($ThisPlanet->accuracy(), 2).'</span> %</td>
						</tr>';
				}
				echo '</table>';
			} ?>
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