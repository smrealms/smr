You are ranked as a <span style="font-size: 125%; color: greenyellow;"><?php echo $ThisAccount->getRank()->name; ?></span> player with a score of <span class="green"><?php echo number_format($ThisAccount->getScore()); ?></span>.<br /><br />

<table class="standard">
	<tr>
		<th>Rank</th>
		<th>Points Required</th>
	</tr>
	<?php
	foreach (Smr\UserRanking::cases() as $rank) { ?>
		<tr>
			<td><?php echo $rank->name; ?></td>
			<td class="center"><?php echo $rank->getMinScore(); ?></td>
		</tr><?php
	} ?>
</table>
<br />

<b>Extended Scores</b>
<br /><?php
foreach ($ThisAccount->getIndividualScores() as $statScore) {
	echo implode(' - ', $statScore['Stat']); ?>, has a stat of <?php echo number_format($ThisAccount->getHOF($statScore['Stat'])); ?> and a score of <span class="green"><?php echo number_format(round($statScore['Score'])); ?></span><br /><?php
}

if (Smr\Session::getInstance()->hasGame()) { ?>
	<br />
	<b>Current Game Extended Stats</b>
	<br /><?php
	foreach ($ThisAccount->getIndividualScores($ThisPlayer) as $statScore) {
		echo implode(' - ', $statScore['Stat']); ?>, has a stat of <?php echo number_format($ThisPlayer->getHOF($statScore['Stat'])); ?> and a score of <span class="green"><?php echo number_format(round($statScore['Score'])); ?></span><br /><?php
	}
} ?>

<br />Note: The total score will be lower than the sum of the individual scores as the points you get for each stat is reduced as you do it more (people who are good at all parts of the game get more points than someone who is only good at one part).
