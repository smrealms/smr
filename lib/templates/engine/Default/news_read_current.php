<?php
$this->includeTemplate('includes/CommonNews.inc');

if(isset($NewsItems) && count($NewsItems) > 0)
{ ?>
	<div align="center">
		<b><big><font color="blue">You have <?php echo count($NewsItems); ?> news entries.</font></big></b>
	</div>
	
	<table class="standard">
		<tr>
			<th align="center">Time</span>
			<th align="center">News</span>
		</tr>
		<?php
		foreach($NewsItems as $NewsItem)
		{ ?>
			<tr>
				<td align="center"><?php echo date(DATE_FULL_SHORT, $NewsItem['Time']); ?></td>
				<td style="text-align:left;vertical-align:middle;"><?php echo $NewsItem['Message']; ?></td>
			</tr><?php
		} ?>
		</table><?php
}
else
{
	?>You have no current news.<?php
} ?>