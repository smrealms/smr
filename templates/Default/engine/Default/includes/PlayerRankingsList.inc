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
			<td valign="top" align="center"><?php echo $Ranking['Rank']; ?></td>
			<td valign="top"><?php echo $Ranking['Player']->getLevelName(); ?> <?php echo $Ranking['Player']->getLinkedDisplayName(false); ?></td>
			<td valign="top"><?php echo $ThisPlayer->getColouredRaceName($Ranking['Player']->getRaceID(), true); ?></td>
			<td valign="top"><?php echo $Ranking['Player']->getAllianceName(true); ?></td>
			<td valign="top"><?php echo $Ranking['Value']; ?></td>
		</tr><?php
	} ?>
</table>