<p class="center">Here are the rankings of the races by their experience</p>
<table class="standard center inset">
	<tr>
		<th class="shrink">Rank</th>
		<th>Race</th>
		<th>Total Experience</th>
		<th>Average Experience</th>
		<th>Total Players</th>
	</tr>

	<?php
	foreach ($Ranks as $rank => $data) { ?>
		<tr>
			<td <?php echo $data['style']; ?>><?php echo $rank + 1; ?></td>
			<td <?php echo $data['style']; ?>><?php echo $ThisPlayer->getColouredRaceName($data['race_id'], true); ?></td>
			<td <?php echo $data['style']; ?>><?php echo $data['exp_sum']; ?></td>
			<td <?php echo $data['style']; ?>><?php echo $data['exp_avg']; ?></td>
			<td <?php echo $data['style']; ?>><?php echo $data['num_players']; ?></td>
		</tr><?php
	} ?>
</table>
