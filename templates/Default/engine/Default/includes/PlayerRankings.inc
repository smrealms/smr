<div align="center">
	<p>Here are the rankings of players by their <?php echo strtolower($RankingStat); ?>.</p>
	<p>The traders listed in <span class="italic">italics</span> are still ranked as Newbie or Beginner.</p>
	<p>You are ranked <?php echo number_format($OurRank); ?> out of <?php echo number_format($TotalRanks); ?> players.</p>
	<?php $this->includeTemplate('includes/PlayerRankingsList.inc', array('RankingStat' => $RankingStat, 'Rankings' => $Rankings)); ?>
	<form method="POST" action="<?php echo $FilterRankingsHREF; ?>">
		<p>
			<input type="number" name="min_rank" value="<?php echo $MinRank; ?>" size="3" class="InputFields center">&nbsp;-&nbsp;<input type="number" name="max_rank" value="<?php echo $MaxRank; ?>" size="3" class="InputFields center">&nbsp;
			<input type="submit" name="action" value="Show" class="InputFields" />
		</p>
	</form>
	<?php $this->includeTemplate('includes/PlayerRankingsList.inc', array('RankingStat' => $RankingStat, 'Rankings' => $FilteredRankings)); ?>
</div>
