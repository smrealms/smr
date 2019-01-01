You are ranked as a <font size="4" color="greenyellow"><?php echo $ThisAccount->getRankName(); ?></font> player with a score of <span class="green"><?php echo number_format($ThisAccount->getScore()); ?></span>.<p>

<table class="standard">
	<tr>
		<th>Rank</th>
		<th>Points Required</th>
	</tr>
	<?php
	foreach (Globals::getUserRanking() as $rankID => $rankName) { ?>
		<tr>
			<td><?php echo $rankName; ?></td>
			<td class="center"><?php echo ceil(pow((max(0,$rankID-1))*SmrAccount::USER_RANKINGS_RANK_BOUNDARY,1/SmrAccount::USER_RANKINGS_TOTAL_SCORE_POW)); ?></td>
		</tr><?php
	} ?>
</table>
<br />

<b>Extended Scores</b>
<br /><?php
foreach ($ThisAccount->getIndividualScores() as $statScore) {
	echo join(' - ', $statScore['Stat']); ?>, has a stat of <?php echo number_format($ThisAccount->getHOF($statScore['Stat'])); ?> and a score of <span class="green"><?php echo number_format(round($statScore['Score'])); ?></span><br /><?php
}

if (SmrSession::hasGame()) { ?>
	<br />
	<b>Current Game Extended Stats</b>
	<br /><?php
	foreach ($ThisAccount->getIndividualScores($ThisPlayer) as $statScore) {
		echo join(' - ', $statScore['Stat']); ?>, has a stat of <?php echo number_format($ThisPlayer->getHOF($statScore['Stat'])); ?> and a score of <span class="green"><?php echo number_format(round($statScore['Score'])); ?></span><br /><?php
	}
} ?>

<br />Note: The total score will be lower than the sum of the individual scores as the points you get for each stat is reduced as you do it more (people who are good at all parts of the game get more points than someone who is only good at one part).
