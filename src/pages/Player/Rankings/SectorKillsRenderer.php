<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\Pages\Shared\SectorKillListRenderer;

class SectorKillsRenderer {

	/**
	 * @param array<int, array{Class: string, SectorID: int, Value: int}> $TopTen
	 * @param array<int, array{Class: string, SectorID: int, Value: int}> $TopCustom
	 */
	public static function render(array $TopTen, string $SubmitHREF, array $TopCustom, int $MinRank, int $MaxRank): void {
		?>
		<div class="center">
			<p>Here are the most deadly Sectors!</p>
			<?php SectorKillListRenderer::render(Rankings: $TopTen); ?>

			<form method="POST" action="<?php echo $SubmitHREF; ?>">
				<p>
					<input type="number" name="min_rank" value="<?php echo $MinRank; ?>" size="3" class="center">&nbsp;-&nbsp;
					<input type="number" name="max_rank" value="<?php echo $MaxRank; ?>" size="3" class="center">&nbsp;
					<?php echo create_submit_display('Show'); ?>
				</p>
			</form>

			<?php SectorKillListRenderer::render(Rankings: $TopCustom); ?>
		</div>

		<?php
	}

}
