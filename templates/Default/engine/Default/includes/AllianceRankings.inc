<div align="center">
	<p>Here are the rankings of alliances by their <?php echo strtolower($RankingStat); ?>.</p>
	<p>You alliance is ranked <?php echo number_format($OurRank); ?> out of <?php echo number_format($TotalRanks); ?> alliances.</p>
	<?php $this->includeTemplate('includes/AllianceRankingsList.inc', array('RankingStat' => $RankingStat, 'Rankings' => $Rankings)); ?>
	<form method="POST" action="<?php echo $FilterRankingsHREF; ?>">
		<p>
			<input type="text" name="min_rank" value="<?php echo $MinRank; ?>" size="3" id="InputFields" class="center">&nbsp;-&nbsp;<input type="text" name="max_rank" value="<?php echo $MaxRank; ?>" size="3" id="InputFields" class="center">&nbsp;
			<input type="submit" name="action" value="Show" id="InputFields" />
		</p>
	</form>
	<?php $this->includeTemplate('includes/AllianceRankingsList.inc', array('RankingStat' => $RankingStat, 'Rankings' => $FilteredRankings)); ?>
</div>