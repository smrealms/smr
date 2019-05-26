<?php
$this->includeTemplate('includes/CommonNews.inc');

if (!empty($NewsItems)) { ?>
	<div class="center">
		Showing most recent <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
	</div><?php
	$this->includeTemplate('includes/NewsTable.inc');
} else {
	?>You have no current news.<?php
} ?>
