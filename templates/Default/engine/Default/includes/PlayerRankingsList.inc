<table class="standard" width="95%">
	<tr>
		<th>Rank</th>
		<th>Player</th>
		<th>Race</th>
		<th>Alliance</th>
		<th><?php echo $RankingStat; ?></th>
	</tr><?php
	foreach($Rankings as $Ranking) { ?>
		<tr<?php echo $Ranking['Class']; ?>>
			<td style="vertical-align:top;text-align:center;"><?php echo $Ranking['Rank']; ?></td>
			<td style="vertical-align:top;"><?php echo $Ranking['Player']->getLevelName(); ?> <?php echo $Ranking['Player']->getLinkedDisplayName(false); ?></td>
			<td style="vertical-align:top;"><?php echo $ThisPlayer->getColouredRaceName($Ranking['Player']->getRaceID(), true); ?></td>
			<td style="vertical-align:top;"><?php echo $Ranking['Player']->getAllianceName(true); ?></td>
			<td style="vertical-align:top;"><?php echo number_format($Ranking['Value']); ?></td>
		</tr><?php
	} ?>
</table>