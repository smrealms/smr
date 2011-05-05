<?php
if (isset($ErrorMsg))
{
	echo $ErrorMsg; ?><br /><?php
}
if (isset($Msg))
{
	echo $Msg; ?><br /><?php
}
?>
<table class="standardnobord fullwidth">
	<tr>
		<td style="width:50%">
			<table class="standard">
				<tr>
					<th width="125">&nbsp;</th>
					<th width="75">Current</th>
					<th width="75">Max</th>
				</tr>
				
				<tr>
					<td>Planet Level</td>
					<td align="center"><span id="planetLevel"><?php echo number_format($ThisPlanet->getLevel(),2); ?></span></td>
					<td align="center">70.00</td>
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
					<td>Generator</td>
					<td align="center"><span id="planetGens"><?php echo $ThisPlanet->getBuilding(PLANET_GENERATOR); ?></span></td>
					<td align="center"><?php echo $ThisPlanet->getMaxBuildings(PLANET_GENERATOR); ?></td>
				</tr>
				<tr>
					<td>Hangar</td>
					<td align="center"><span id="planetHangars"><?php echo $ThisPlanet->getBuilding(PLANET_HANGAR); ?></span></td>
					<td align="center"><?php echo $ThisPlanet->getMaxBuildings(PLANET_HANGAR); ?></td>
				</tr>
				<tr>
					<td>Turret</td>
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
					<td align="center"><span id="planetAcc"><?php echo $ThisPlanet->accuracy() ?></span> %</td>
				</tr>
			</table>
		</td><?php
		if(isset($Ticker))
		{ ?>
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