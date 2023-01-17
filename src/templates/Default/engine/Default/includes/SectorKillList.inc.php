<?php declare(strict_types=1);

/**
 * @var array<int, array{Class: string, SectorID: int, Value: int}> $Rankings
 */

?>
<table class="standard center" width="45%">
	<tr>
		<th class="shrink">Rank</th>
		<th>Sector</th>
		<th>Battles</th>
	</tr><?php
	foreach ($Rankings as $Rank => $Ranking) { ?>
		<tr<?php echo $Ranking['Class']; ?>>
			<td><?php echo $Rank; ?></td>
			<td><?php echo $Ranking['SectorID']; ?></td>
			<td><?php echo $Ranking['Value']; ?></td>
		</tr><?php
	} ?>
</table>
