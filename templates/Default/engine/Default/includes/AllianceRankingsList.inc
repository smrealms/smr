<table class="standard center inset">
	<tr>
		<th class="shrink">Rank</th>
		<th>Alliance</th>
		<th>Total <?php echo $RankingStat; ?></th>
		<th>Average <?php echo $RankingStat; ?></th>
		<th>Total Members</th>
	</tr><?php
	foreach($Rankings as $Ranking) { ?>
		<tr<?php echo $Ranking['Class']; ?>>
			<td class="top"><?php echo $Ranking['Rank']; ?></td>
			<td class="top left"><?php echo $Ranking['Alliance']->getAllianceName(true); ?></td>
			<td class="top"><?php echo number_format($Ranking['Value']); ?></td>
			<td class="top"><?php echo number_format($Ranking['Value'] / max(1, $Ranking['Alliance']->getNumMembers())); ?></td>
			<td class="top"><?php echo $Ranking['Alliance']->getNumMembers(); ?></td>
		</tr><?php
	} ?>
</table>
