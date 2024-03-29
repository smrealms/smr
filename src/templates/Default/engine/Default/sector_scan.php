<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 * @var Smr\Sector $ScanSector
 * @var int $EnemyVessel
 * @var int $FriendlyVessel
 * @var int $EnemyForces
 * @var int $FriendlyForces
 * @var int $Turns
 */

?>
<table class="standard">
	<tr>
		<th></th>
		<th>Enemy Scan</th>
		<th>Friendly Scan</th>
	</tr>
	<tr>
		<td>Vessels</td>
		<td class="center"><?php echo $EnemyVessel; ?></td>
		<td class="center"><?php echo $FriendlyVessel; ?></td>
	</tr>
	<tr>
		<td>Forces</td>
		<td class="center"><?php echo $EnemyForces; ?></td>
		<td class="center"><?php echo $FriendlyForces; ?></td>
	</tr>
</table>
<br />

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
<br />

<a href="<?php echo $ScanSector->getSectorScanHREF($ThisPlayer); ?>" class="submitStyle">Rescan #<?php echo $ScanSector->getSectorID(); ?></a>&nbsp;
<a href="<?php echo $ScanSector->getCurrentSectorMoveHREF($ThisPlayer); ?>" class="submitStyle">Enter #<?php echo $ScanSector->getSectorID(); ?> (<?php echo $Turns; ?>)</a>
