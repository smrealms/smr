<div align="center">
	<p>Here are the rankings of players by their <?php echo $RankingStat; ?></p>
	<p>The traders listed in <span class="italic">italics</span> are still ranked as Newbie or Beginner.</p>
	<p>You are ranked <?php echo $OurRank; ?> out of <?php echo $TotalPlayers; ?></p>
	<?php $this->includeTemplate('includes/PlayerRankingsList.inc', array('RankingStat' => $RankingStat, 'Rankings' => $Rankings)); ?>
	<form method="POST" action="<?php echo $FilterRankingsHREF; ?>">
		<p>
			<input type="text" name="min_rank" value="<?php echo $MinRank; ?>" size="3" id="InputFields" class="center">&nbsp;-&nbsp;<input type="text" name="max_rank" value="<?php echo $MaxRank; ?>" size="3" id="InputFields" class="center">&nbsp;
			<input type="submit" name="action" value="Show" id="InputFields" />
		</p>
	</form>
	<?php $this->includeTemplate('includes/PlayerRankingsList.inc', array('RankingStat' => $RankingStat, 'Rankings' => $FilteredRankings)); ?>
</div>