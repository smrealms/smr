<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Pages\Shared\CommonNewsRenderer;
use Smr\Pages\Shared\NewsTableRenderer;

class NewsReadArchivesRenderer {

/**
 * @param array<array{Date: string, Message: string}> $NewsItems
 * @param ?array{Time: int, Message: string} $BreakingNews
 * @param ?array{Time: int, Message: string} $LottoNews
 */
public static function render(
	int $MinNews,
	int $MaxNews,
	string $ViewNewsFormHref,
	array $NewsItems,
	Account $ThisAccount,
	?array $BreakingNews,
	?array $LottoNews,
): void {
CommonNewsRenderer::render(
	ThisAccount: $ThisAccount,
	BreakingNews: $BreakingNews,
	LottoNews: $LottoNews,
); ?>

<div class="center">View News entries</div><br />
<form name="ViewNewsForm" method="POST" action="<?php echo $ViewNewsFormHref; ?>">
	<div class="center">
		<input type="number" name="min_news" value="<?php echo $MinNews; ?>" class="center">
		&nbsp;-&nbsp;
		<input type="number" name="max_news" value="<?php echo $MaxNews; ?>" class="center">&nbsp;<br />
		<?php echo create_submit_display('View'); ?>
	</div>
</form>

<?php
if (count($NewsItems) > 0) { ?>
	<br />
	<div class="center">
		Showing <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
	</div><?php
	NewsTableRenderer::render(NewsItems: $NewsItems);
} else {
	?>No news to read.<?php
}

}

}
