<?php $this->includeTemplate('includes/CommonNews.inc'); ?>

<div class="center">View News entries</div><br />
<form name="ViewNewsForm" method="POST" action="<?php echo $ViewNewsFormHref; ?>">
	<div class="center">
		<input type="number" name="min_news" value="<?php echo $MinNews; ?>" class="center">
		&nbsp;-&nbsp;
		<input type="number" name="max_news" value="<?php echo $MaxNews; ?>" class="center">&nbsp;<br />
		<input type="submit" name="action" value="View" />
	</div>
</form>

<?php
if (!empty($NewsItems)) { ?>
	<br />
	<div class="center">
		Showing <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
	</div><?php
	$this->includeTemplate('includes/NewsTable.inc');
} else {
	?>No news to read.<?php
} ?>
