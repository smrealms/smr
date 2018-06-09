<p>
	<table class="standard">
		<tr>
			<th>&nbsp;</th>
			<th align="center">Scan Results</th>
		</tr>
		<tr>
			<td>Friendly vessels</td>
			<td align="center"><?php echo $FriendlyVessel; ?></td>
		</tr>
		<tr>
			<td>Enemy vessels</td>
			<td align="center"><?php echo $EnemyVessel; ?></td>
		</tr>
		<tr>
			<td>Friendly forces</td>
			<td align="center"><?php echo $FriendlyForces; ?></td>
		</tr>
		<tr>
			<td>Enemy forces</td>
			<td align="center"><?php echo $EnemyForces; ?></td>
		</tr>
	</table>
</p>

<p>
	<table class="standard">
		<tr>
			<td>Planet</td>
			<td><?php echo $ScanSector->hasPlanet() ? 'Yes' : 'No'; ?></td>
		</tr>
		<tr>
			<td>Port</td>
			<td><?php echo $ScanSector->hasPort() ? 'Yes' : 'No'; ?></td>
		</tr>
		<tr>
			<td>Location</td>
			<td><?php echo $ScanSector->hasLocation() ? 'Yes' : 'No'; ?></td>
		</tr>
	</table>
</p>
<br />

<a href="<?php echo $ScanSector->getScanSectorHREF(); ?>" class="submitStyle">Rescan <?php echo $ScanSector->getSectorID(); ?></a>&nbsp;
<a href="<?php echo $ScanSector->getCurrentSectorMoveHREF(); ?>" class="submitStyle">Enter <?php echo $ScanSector->getSectorID(); ?> (<?php echo $Turns; ?>)</a>
