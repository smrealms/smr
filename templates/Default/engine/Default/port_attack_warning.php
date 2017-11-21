<span class="red">WARNING WARNING</span> port assault about to commence!!<br />

<br />
Suddenly, sirens sound and warning lights flash as your onboard sensors detect
that the port has enough defensive firepower to reduce your ship to space debris.
Without an armada behind you, the outcome may not be pleasant.
<a href="<?php echo WIKI_URL; ?>/game-guide/combat#ports" target="_blank"><img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Port Combat"/></a>
<br /><br />

<?php
if ($ThisShip->hasScanner()) {
	$Port = $ThisSector->getPort(); ?>
<p>
	<table class="standard">
		<tr>
			<th>Port</th>
			<th>Scan Results</th>
		<tr>
		<tr>
			<td>Shields</td>
			<td align="center"><?php echo $Port->getShields(); ?></td>
		</tr>
		<tr>
			<td>Combat Drones</td>
			<td align="center"><?php echo $Port->getCDs(); ?></td>
		</tr>
		<tr>
			<td>Armour</td>
			<td align="center"><?php echo $Port->getArmour(); ?></td>
		</tr>
		<tr>
			<td>Turrets</td>
			<td align="center"><?php echo $Port->getNumWeapons(); ?></td>
		</tr>
	</table>
</p><?php
} ?>

Are you sure you want to attack this port?<br /><br />

<div class="buttonA">
	<a class="buttonA" href="<?php echo $PortAttackHREF; ?>">&nbsp;Yes&nbsp;</a>
</div>&nbsp;
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getCurrentSectorHREF(); ?>">&nbsp;No&nbsp;</a>
</div>
