<table class="standard center inset">
	<tr>
		<th class="shrink">Rank</th>
		<th>Player</th>
		<th>Race</th>
		<th>Alliance</th>
		<th><?php echo $RankingStat; ?></th>
	</tr><?php
	foreach ($Rankings as $Ranking) { ?>
		<tr<?php echo $Ranking['Class']; ?>>
			<td class="top"><?php echo $Ranking['Rank']; ?></td>
			<td class="top left"><?php echo $Ranking['Player']->getLevelName(); ?> <?php echo $Ranking['Player']->getLinkedDisplayName(false); ?></td>
			<td class="top"><?php echo $ThisPlayer->getColouredRaceName($Ranking['Player']->getRaceID(), true); ?></td>
			<td class="top"><?php echo $Ranking['Player']->getAllianceName(true); ?></td>
			<td class="top right"><?php echo number_format($Ranking['Value']); ?></td>
		</tr><?php
	} ?>
</table>
