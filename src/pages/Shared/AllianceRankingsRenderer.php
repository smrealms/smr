<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

class AllianceRankingsRenderer {

	/**
	 * @param array<int, array{Alliance: \Smr\Alliance, Class: string, Value: int}> $Rankings
	 * @param array<int, array{Alliance: \Smr\Alliance, Class: string, Value: int}> $FilteredRankings
	 */
	public static function render(
		string $RankingStat,
		int $MinRank,
		int $MaxRank,
		?int $OurRank,
		int $TotalRanks,
		array $Rankings,
		array $FilteredRankings,
		string $FilterRankingsHREF,
	): void {
		?>
		<div class="center">
			<p>Here are the rankings of alliances by their <?php echo $RankingStat; ?>.</p><?php
			if (isset($OurRank)) { ?>
				<p>Your alliance is ranked <?php echo number_format($OurRank); ?> out of <?php echo number_format($TotalRanks); ?> alliances.</p><?php
			}
			AllianceRankingsListRenderer::render(RankingStat: $RankingStat, Rankings: $Rankings); ?>
			<form method="POST" action="<?php echo $FilterRankingsHREF; ?>">
				<p>
					<input type="number" name="min_rank" value="<?php echo $MinRank; ?>" size="3" class="center">&nbsp;-&nbsp;<input type="number" name="max_rank" value="<?php echo $MaxRank; ?>" size="3" class="center">&nbsp;
					<?php echo create_submit_display('Show'); ?>
				</p>
			</form>
			<?php AllianceRankingsListRenderer::render(RankingStat: $RankingStat, Rankings: $FilteredRankings); ?>
		</div>

		<?php
	}

}
