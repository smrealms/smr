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
			&nbsp;<?php echo $ThisPlanet->getTypeName() ?>: <?php echo $ThisPlanet->getTypeDescription() ?>
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
					<th width="125">&nbsp;</th>
					<th width="75">Current</th>
					<th width="75">Max</th>
				</tr>
				<tr>
					<td>Generator <img class="tooltip" src="images/silk/information.png"  width="16" height="16" alt="Information" title="Generators protect a planet with shields. Each generator can hold 100 shields and planets can have a maximum of 100 generators, or 10,000 shields"/></td>
					<td align="center"><span id="planetGens"><?php echo $ThisPlanet->getBuilding(PLANET_GENERATOR); ?></span></td>
					<td align="center"><?php echo $ThisPlanet->getMaxBuildings(PLANET_GENERATOR); ?></td>
				</tr>
				<tr>
					<td>Hangar <img class="tooltip" src="images/silk/information.png" width="16" height="16" alt="Information" title="Hangars house and launch combat drones(CDs). Each hangar holds 20 drones and planets can have a maximum of 100 hangars, or 2,000 CDs."/></td>
					<td align="center"><span id="planetHangars"><?php echo $ThisPlanet->getBuilding(PLANET_HANGAR); ?></span></td>
					<td align="center"><?php echo $ThisPlanet->getMaxBuildings(PLANET_HANGAR); ?></td>
				</tr>
				<tr>
					<td>Turret <img class="tooltip" src="images/silk/information.png"  width="16" height="16" alt="Information" title="Turrets fire heavy laser beams. Each planet can have a maximum of 10 turrets. These laser beams do 250/250 damage. When they fire at an attacking ship, they can destroy 250 shields, or 250 armor (but not both on the same shot)."/></td>
					<td align="center"><span id="planetTurrets1"><?php echo $ThisPlanet->getBuilding(PLANET_TURRET); ?></span></td>
					<td align="center"><?php echo $ThisPlanet->getMaxBuildings(PLANET_TURRET); ?></td>
				</tr>
			</table>
			<br />
			<table class="standard">
				<tr>
					<th width="125">&nbsp;</th>
					<th width="75">Amount</th>
					<th width="75">Accuracy</th>
				</tr>
				<tr>
					<td>Shields</td>
					<td align="center"><span id="planetShields"><?php echo $ThisPlanet->getShields(); ?></span> / <span id="planetMaxShields"><?php echo $ThisPlanet->getMaxShields(); ?></span></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Combat Drones</td>
					<td align="center"><span id="planetCDs"><?php echo $ThisPlanet->getCDs(); ?></span> / <span id="planetMaxCDs"><?php echo $ThisPlanet->getMaxCDs(); ?></span></td>
					<td align="center">100 %</td>
				</tr>
				<tr>
					<td>Turrets</td>
					<td align="center"><span id="planetTurrets2"><?php echo $ThisPlanet->getBuilding(PLANET_TURRET) ?></span> / <?php echo $ThisPlanet->getMaxBuildings(PLANET_TURRET); ?></td>
					<td align="center"><span id="planetAcc"><?php echo number_format($ThisPlanet->accuracy(), 2) ?></span> %</td>
				</tr>
			</table>
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