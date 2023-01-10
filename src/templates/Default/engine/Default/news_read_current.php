<?php declare(strict_types=1);

$this->includeTemplate('includes/CommonNews.inc.php');

if (!empty($NewsItems)) { ?>
	<div class="center">
		Showing most recent <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
	</div><?php
	$this->includeTemplate('includes/NewsTable.inc.php');
} else {
	?>You have no current news.<?php
}
