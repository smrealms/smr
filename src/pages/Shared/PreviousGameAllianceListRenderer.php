<?php declare(strict_types=1);

/**
 * @var string $BackHREF
 * @var array<array{leader: string, bold: string, player_name: string, experience: int, alignment: int, race: string, kills: int, deaths: int, bounty: int}> $Players
 */

?>
<div class="center">
	<a href="<?php echo $BackHREF; ?>"><b>&lt;&lt;Back</b></a>

	<table class="center standard">
		<tr>
			<th></th>
			<th>Player Name</th>
			<th>Experience</th>
			<th>Alignment</th>
			<th>Race</th>
			<th>Kills</th>
			<th>Deaths</th>
			<th>Bounty</th>
		</tr><?php
		foreach ($Players as $i => $player) { ?>
			<tr <?php echo $player['bold']; ?>>
				<td><?php echo ($i + 1) . $player['leader']; ?></td>
				<td><?php echo $player['player_name']; ?></td>
				<td><?php echo number_format($player['experience']); ?></td>
				<td><?php echo number_format($player['alignment']); ?></td>
				<td><?php echo $player['race']; ?></td>
				<td><?php echo number_format($player['kills']); ?></td>
				<td><?php echo number_format($player['deaths']); ?></td>
				<td><?php echo number_format($player['bounty']); ?></td>
			</tr><?php
		} ?>
	</table>
</div>
