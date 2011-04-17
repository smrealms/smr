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
					<td align="center"><?php echo number_format($ThisPlanet->getLevel(),2); ?></td>
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
					<td align="center"><?php echo $ThisPlanet->getBuilding(PLANET_GENERATOR); ?></td>
					<td align="center"><?php echo $ThisPlanet->getMaxBuildings(PLANET_GENERATOR); ?></td>
				</tr>
				<tr>
					<td>Hangar</td>
					<td align="center"><?php echo $ThisPlanet->getBuilding(PLANET_HANGAR); ?></td>
					<td align="center"><?php echo $ThisPlanet->getMaxBuildings(PLANET_HANGAR); ?></td>
				</tr>
				<tr>
					<td>Turret</td>
					<td align="center"><?php echo $ThisPlanet->getBuilding(PLANET_TURRET); ?></td>
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
					<td align="center"><?php echo $ThisPlanet->getShields(); ?> / <?php echo $ThisPlanet->getMaxShields(); ?></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Combat Drones</td>
					<td align="center"><?php echo $ThisPlanet->getCDs(); ?> / <?php echo $ThisPlanet->getMaxCDs(); ?></td>
					<td align="center">100 %</td>
				</tr>
				<tr>
					<td>Turrets</td>
					<td align="center"><?php echo $ThisPlanet->getBuilding(PLANET_TURRET) ?> / <?php echo $ThisPlanet->getMaxBuildings(PLANET_TURRET); ?></td>
					<td align="center"><?php echo $ThisPlanet->accuracy() ?> %</td>
				</tr>
			</table>
		</td><?php
		if(isset($PlanetPlayers) || isset($Ticker))
		{ ?>
			<td><?php
				$this->includeTemplate('includes/Ticker.inc');
				if(isset($PlanetPlayers))
				{
					foreach($PlanetPlayers as $PlanetPlayer)
					{
						if(isset($PlanetPlayer['KickFormLink']))
						{
							?><form name="KickPlayerForm" method="POST" action="<?php echo $PlanetPlayer['KickFormLink']; ?>"><?php
						} ?>
						<a href="<?php echo $PlanetPlayer['SearchLink']; ?>"><span class="yellow"><?php echo $PlanetPlayer['Player']->getPlayerName(); ?></span></a><br /><?php
						if(isset($PlanetPlayer['KickFormLink']))
						{
							?><input type="submit" name="action" value="Kick" id="InputFields">
							</form><?php
						}
					}
				} ?>
			</td><?php
		} ?>
	</tr>
</table>
<br />
<form name="LaunchForm" method="POST" action="<?php echo $LaunchFormLink; ?>">
	<input type="submit" name="action" value="Launch" id="InputFields"/>
</form>