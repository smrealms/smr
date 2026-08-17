<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

class ExtendedStatsDetailRenderer {

/**
 * @param array<array{bold: string, data: array<float|int|string>}> $Rankings
 * @param array<string> $Headers
 */
public static function render(string $BackHREF, array $Rankings, array $Headers): void {
?>
<div class="center">
	<a href="<?php echo $BackHREF; ?>"><b>&lt;&lt;Back</b></a>
	<table class="center standard">
		<tr>
			<th>Rank</th><?php
			foreach ($Headers as $Header) { ?>
				<th><?php echo $Header; ?></th><?php
			} ?>
		</tr><?php
		foreach ($Rankings as $index => $row) { ?>
			<tr <?php echo $row['bold']; ?>>
				<td><?php echo $index + 1; ?></td><?php
				foreach ($row['data'] as $value) { ?>
					<td><?php echo $value; ?></td><?php
				} ?>
			</tr><?php
		} ?>
	</table>
</div>

<?php
}

}
