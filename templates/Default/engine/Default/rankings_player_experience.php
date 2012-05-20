<div align="center">
	<p>Here are the rankings of players by their <?php echo $RankingStat; ?></p>
	<p>The traders listed in <span class="italic">italics</span> are still ranked as Newbie or Beginner.</p>
	<p>You are ranked <?php echo $OurRank; ?> out of <?php echo $TotalPlayers; ?></p>
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
	<form method="POST" action="<?php echo $FilterRankingsHREF; ?>">
		<p>
			<input type="text" name="min_rank" value="<?php echo $MinRank; ?>" size="3" id="InputFields" class="center">&nbsp;-&nbsp;<input type="text" name="max_rank" value="<?php echo $MaxRank; ?>" size="3" id="InputFields" class="center">&nbsp;
			<input type="submit" name="action" value="Show" id="InputFields" />
		</p>
	</form>
	<table class="standard" width="95%">
		<tr>
			<th>Rank</th>
			<th>Player</th>
			<th>Race</th>
			<th>Alliance</th>
			<th><?php echo $RankingStat; ?></th>
		</tr><?php
		foreach($FilteredRankings as $Ranking) { ?>
			<tr<?php echo $Ranking['Class']; ?>>
				<td valign="top" align="center"><?php echo $Ranking['Rank']; ?></td>
				<td valign="top"><?php echo $Ranking['Player']->getLevelName(); ?> <?php echo $Ranking['Player']->getLinkedDisplayName(false); ?></td>
				<td valign="top"><?php echo $ThisPlayer->getColouredRaceName($Ranking['Player']->getRaceID(), true); ?></td>
				<td valign="top"><?php echo $Ranking['Player']->getAllianceName(true); ?></td>
				<td valign="top"><?php echo $Ranking['Value']; ?></td>
			</tr><?php
		} ?>
	</table>
</div>