<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

class NewsTableRenderer {

	/**
	 * @param array<array{Date: string, Message: string}> $NewsItems
	 */
	public static function render(array $NewsItems): void {
		?>
		<table class="standard fullwidth">
			<tr>
				<th class="shrink">Time</th>
				<th>News</th>
			</tr>
			<?php
			foreach ($NewsItems as $NewsItem) { ?>
				<tr>
					<td class="center noWrap"><?php echo $NewsItem['Date']; ?></td>
					<td><?php echo $NewsItem['Message']; ?></td>
				</tr><?php
			} ?>
		</table>

		<?php
	}

}
