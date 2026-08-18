<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Player;

class PlayerRankingsRenderer {

	/**
	 * @param array<int, array{Player: \Smr\Player, Class: string, Value: int}> $Rankings
	 * @param array<int, array{Player: \Smr\Player, Class: string, Value: int}> $FilteredRankings
	 */
	public static function render(
		string $RankingStat,
		int $OurRank,
		int $MaxRank,
		int $MinRank,
		int $TotalRanks,
		array $Rankings,
		array $FilteredRankings,
		string $FilterRankingsHREF,
		Player $ThisPlayer,
	): void {
		?>
		<div class="center">
			<p>Here are the rankings of players by their <?php echo $RankingStat; ?>.</p>
			<p>The traders listed in <span class="italic">italics</span> are still ranked as Newbie or Beginner.</p>
			<p>You are ranked <?php echo number_format($OurRank); ?> out of <?php echo number_format($TotalRanks); ?> players.</p>
			<?php PlayerRankingsListRenderer::render(
				RankingStat: $RankingStat,
				Rankings: $Rankings,
				ThisPlayer: $ThisPlayer,
			); ?>
			<form method="POST" action="<?php echo $FilterRankingsHREF; ?>">
				<p>
					<input type="number" name="min_rank" value="<?php echo $MinRank; ?>" size="3" class="center">&nbsp;-&nbsp;<input type="number" name="max_rank" value="<?php echo $MaxRank; ?>" size="3" class="center">&nbsp;
					<?php echo create_submit_display('Show'); ?>
				</p>
			</form>
			<?php PlayerRankingsListRenderer::render(
				RankingStat: $RankingStat,
				Rankings: $FilteredRankings,
				ThisPlayer: $ThisPlayer,
			); ?>
		</div>

		<?php
	}

}
