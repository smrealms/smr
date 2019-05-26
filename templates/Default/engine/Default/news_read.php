<?php $this->includeTemplate('includes/CommonNews.inc'); ?>

<div class="center">View News entries</div><br />
<form name="ViewNewsForm" method="POST" action="<?php echo $ViewNewsFormHref; ?>">
	<div class="center">
		<input type="text" name="min_news" value="1" size="3" class="InputFields center">&nbsp;-&nbsp;<input type="text" name="max_news" value="50" size="3" class="InputFields center">&nbsp;<br />
		<input type="submit" name="action" value="View" class="InputFields" />
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
