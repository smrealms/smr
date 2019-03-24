<?php $this->includeTemplate('includes/CommonNews.inc'); ?>

<div class="center">View News entries</div><br />
<form name="ViewNewsForm" method="POST" action="<?php echo $ViewNewsFormHref; ?>">
	<div class="center">
		<input type="text" name="min_news" value="1" size="3" class="InputFields center">&nbsp;-&nbsp;<input type="text" name="max_news" value="50" size="3" class="InputFields center">&nbsp;<br />
		<input type="submit" name="action" value="View" class="InputFields" />
	</div>
</form>

<?php
if(isset($NewsItems) && count($NewsItems) > 0) { ?>
	<br />
	<div class="center">
		Showing <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
	</div>
	<table class="standard">
		<tr>
			<th class="center">Time</th>
			<th class="center">News</th>
		</tr>
		<?php
		foreach($NewsItems as $NewsItem) { ?>
			<tr>
				<td class="center"><?php echo date(DATE_FULL_SHORT, $NewsItem['Time']); ?></td>
				<td><?php echo $NewsItem['Message']; ?></td>
			</tr><?php
		} ?>
		</table><?php
}
else {
	?>No news to read.<?php
} ?>
