<p class="center">Here are the rankings of the races by their <?php echo $RankingStat; ?>.</p>
<table class="standard center inset">
	<tr>
		<th class="shrink">Rank</th>
		<th>Race</th>
		<th>Total <?php echo $RankingStat; ?></th>
		<th>Average <?php echo $RankingStat; ?></th>
		<th>Total Players</th>
	</tr>

	<?php
	foreach ($Ranks as $rank => $data) { ?>
		<tr>
			<td <?php echo $data['style']; ?>><?php echo $rank; ?></td>
			<td <?php echo $data['style']; ?>><?php echo $ThisPlayer->getColouredRaceName($data['race_id'], true); ?></td>
			<td <?php echo $data['style']; ?>><?php echo $data['amount']; ?></td>
			<td <?php echo $data['style']; ?>><?php echo $data['amount_avg']; ?></td>
			<td <?php echo $data['style']; ?>><?php echo $data['num_players']; ?></td>
		</tr><?php
	} ?>
</table>
