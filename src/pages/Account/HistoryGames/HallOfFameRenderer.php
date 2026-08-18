<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

class HallOfFameRenderer {

	/**
	 * @param list<string> $Links
	 */
	public static function renderCategories(array $Links): void {
		?>
		<table class="center standard">
			<tr><th>Categories</th></tr><?php
			foreach ($Links as $link) { ?>
				<tr><td><?php echo $link; ?></td></tr><?php
			} ?>
		</table><?php
	}

	/**
	 * @param array<array{bold: string, name: string, stat: int}> $Rankings
	 */
	public static function renderRankings(string $BackHREF, string $StatName, array $Rankings): void {
		?>
		<div class="center"><a href="<?php echo $BackHREF; ?>">&lt;&lt;Back</a></div>

		<?php
		if (count($Rankings) > 0) { ?>
			<table class="shrink center standard">
				<tr>
					<th>Rank</th>
					<th>Player</th>
					<th><?php echo $StatName; ?></th>
				</tr><?php
				foreach ($Rankings as $index => $ranking) { ?>
					<tr <?php echo $ranking['bold']; ?>>
						<td><?php echo $index + 1; ?></td>
						<td class="noWrap"><?php echo htmlentities($ranking['name']); ?></td>
						<td><?php echo number_format($ranking['stat']); ?></td>
					</tr><?php
				} ?>
			</table><?php
		} else { ?>
			<p class="center">We apologize, but this stat does not exist for this game!</p><?php
		}
	}

}
