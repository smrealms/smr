<p class="center">Here are the rankings of the races by their deaths</p>
<table class="standard center" width="40%">
	<tr>
		<th class="shrink">Rank</th>
		<th>Race</th>
		<th>Deaths</th>
	</tr>

	<?php
	foreach ($Ranks as $rank => $data) { ?>
		<tr>
			<td <?php echo $data['style']; ?>><?php echo $rank; ?></td>
			<td <?php echo $data['style']; ?>><?php echo $ThisPlayer->getColouredRaceName($data['race_id'], true); ?></td>
			<td <?php echo $data['style']; ?>><?php echo $data['amount']; ?></td>
		</tr><?php
	} ?>
</table>
