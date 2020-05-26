<div class="center">
	<a href="<?php echo $BackHREF; ?>"><b>&lt;&lt;Back</b></a>

	<table class="center standard">
		<tr>
			<th>Player Name</th>
			<th>Experience</th>
			<th>Alignment</th>
			<th>Race</th>
			<th>Kills</th>
			<th>Deaths</th>
			<th>Bounty</th>
		</tr><?php
		foreach ($Players as $player) { ?>
			<tr <?php echo $player['bold']; ?>>
				<td><?php echo $player['player_name']; ?></td>
				<td><?php echo $player['experience']; ?></td>
				<td><?php echo $player['alignment']; ?></td>
				<td><?php echo $player['race']; ?></td>
				<td><?php echo $player['kills']; ?></td>
				<td><?php echo $player['deaths']; ?></td>
				<td><?php echo $player['bounty']; ?></td>
			</tr><?php
		} ?>
	</table>
</div>
