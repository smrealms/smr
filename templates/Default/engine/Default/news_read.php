<?php $this->includeTemplate('includes/CommonNews.inc'); ?>

<div align="center">View News entries</div><br />
<form name="ViewNewsForm" method="POST" action="<?php echo $ViewNewsFormHref; ?>">
	<div align="center">
		<input type="text" name="min_news" value="1" size="3" id="InputFields" class="center">&nbsp;-&nbsp;<input type="text" name="max_news" value="50" size="3" id="InputFields" class="center">&nbsp;<br />
		<input type="submit" name="action" value="View" id="InputFields" />
	</div>
</form>

<?php
if(isset($NewsItems) && count($NewsItems) > 0) { ?>
	<br />
	<div align="center">
		Showing <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
	</div>
	<table class="standard">
		<tr>
			<th align="center">Time</th>
			<th align="center">News</th>
		</tr>
		<?php
		foreach($NewsItems as $NewsItem) { ?>
			<tr>
				<td align="center"><?php echo date(DATE_FULL_SHORT, $NewsItem['Time']); ?></td>
				<td style="text-align:left;vertical-align:middle;"><?php echo $NewsItem['Message']; ?></td>
			</tr><?php
		} ?>
		</table><?php
}
else {
	?>No news to read.<?php
} ?>