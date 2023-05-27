<?php declare(strict_types=1);

/**
 * @var Smr\Template $this
 * @var array<array{Date: string, Message: string}> $NewsItems
 */

$this->includeTemplate('includes/CommonNews.inc.php');

if (count($NewsItems) > 0) { ?>
	<div class="center">
		Showing most recent <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
	</div><?php
	$this->includeTemplate('includes/NewsTable.inc.php');
} else {
	?>You have no current news.<?php
}
