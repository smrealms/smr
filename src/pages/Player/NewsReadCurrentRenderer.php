<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Account;
use Smr\Pages\Shared\CommonNewsRenderer;
use Smr\Pages\Shared\NewsTableRenderer;

class NewsReadCurrentRenderer {

	/**
	 * @param array<array{Date: string, Message: string}> $NewsItems
	 * @param ?array{Time: int, Message: string} $BreakingNews
	 * @param ?array{Time: int, Message: string} $LottoNews
	 */
	public static function render(
		array $NewsItems,
		Account $ThisAccount,
		?array $BreakingNews,
		?array $LottoNews,
	): void {
		CommonNewsRenderer::render(
			ThisAccount: $ThisAccount,
			BreakingNews: $BreakingNews,
			LottoNews: $LottoNews,
		);

		if (count($NewsItems) > 0) { ?>
			<div class="center">
				Showing most recent <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
			</div><?php
			NewsTableRenderer::render(NewsItems: $NewsItems);
		} else {
			?>You have no current news.<?php
		}

	}

}
