<table class="standard" width="95%">
	<tr>
		<th>Rank</th>
		<th>Alliance</th>
		<th>Total <?php echo $RankingStat; ?></th>
		<th>Average <?php echo $RankingStat; ?></th>
		<th>Total Members</th>
	</tr><?php
	foreach($Rankings as $Ranking) { ?>
		<tr<?php echo $Ranking['Class']; ?>>
			<td style="vertical-align:top;" class="center"><?php echo $Ranking['Rank']; ?></td>
			<td style="vertical-align:top;"><?php echo $Ranking['Alliance']->getAllianceName(true); ?></td>
			<td style="vertical-align:top;" class="center"><?php echo number_format($Ranking['Value']); ?></td>
			<td style="vertical-align:top;" class="center"><?php echo number_format($Ranking['Value'] / max(1, $Ranking['Alliance']->getNumMembers())); ?></td>
			<td style="vertical-align:top;" class="center"><?php echo $Ranking['Alliance']->getNumMembers(); ?></td>
		</tr><?php
	} ?>
</table>