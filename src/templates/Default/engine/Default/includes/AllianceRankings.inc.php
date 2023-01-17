<?php declare(strict_types=1);

/**
 * @var Smr\Template $this
 * @var string $RankingStat
 * @var int $MinRank
 * @var int $MaxRank
 * @var int $TotalRanks
 * @var array<int, array{Alliance: Smr\Alliance, Class: string, Value: int}> $Rankings
 * @var array<int, array{Alliance: Smr\Alliance, Class: string, Value: int}> $FilteredRankings
 * @var string $FilterRankingsHREF
 */

?>
<div class="center">
	<p>Here are the rankings of alliances by their <?php echo $RankingStat; ?>.</p><?php
	if (isset($OurRank)) { ?>
		<p>Your alliance is ranked <?php echo number_format($OurRank); ?> out of <?php echo number_format($TotalRanks); ?> alliances.</p><?php
	}
	$this->includeTemplate('includes/AllianceRankingsList.inc.php', ['RankingStat' => $RankingStat, 'Rankings' => $Rankings]); ?>
	<form method="POST" action="<?php echo $FilterRankingsHREF; ?>">
		<p>
			<input type="number" name="min_rank" value="<?php echo $MinRank; ?>" size="3" class="center">&nbsp;-&nbsp;<input type="number" name="max_rank" value="<?php echo $MaxRank; ?>" size="3" class="center">&nbsp;
			<input type="submit" name="action" value="Show" />
		</p>
	</form>
	<?php $this->includeTemplate('includes/AllianceRankingsList.inc.php', ['RankingStat' => $RankingStat, 'Rankings' => $FilteredRankings]); ?>
</div>
