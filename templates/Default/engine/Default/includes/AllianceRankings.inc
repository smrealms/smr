<div align="center">
	<p>Here are the rankings of alliances by their <?php echo strtolower($RankingStat); ?>.</p><?php
	if (isset($OurRank)) { ?>
		<p>Your alliance is ranked <?php echo number_format($OurRank); ?> out of <?php echo number_format($TotalRanks); ?> alliances.</p><?php
	}
	$this->includeTemplate('includes/AllianceRankingsList.inc', array('RankingStat' => $RankingStat, 'Rankings' => $Rankings)); ?>
	<form method="POST" action="<?php echo $FilterRankingsHREF; ?>">
		<p>
			<input type="number" name="min_rank" value="<?php echo $MinRank; ?>" size="3" class="InputFields center">&nbsp;-&nbsp;<input type="number" name="max_rank" value="<?php echo $MaxRank; ?>" size="3" class="InputFields center">&nbsp;
			<input type="submit" name="action" value="Show" class="InputFields" />
		</p>
	</form>
	<?php $this->includeTemplate('includes/AllianceRankingsList.inc', array('RankingStat' => $RankingStat, 'Rankings' => $FilteredRankings)); ?>
</div>
