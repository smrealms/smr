<table class="standard fullwidth">
	<tr>
		<th>Time</th>
		<th>Message</th>
	</tr>

	<?php
	foreach ($Announcements as $Announcement) { ?>
		<tr>
			<td class="shrink top noWrap">
				<?php echo date(DATE_FULL_SHORT_SPLIT, $Announcement['Time']); ?>
			</td>
			<td class="top">
				<?php echo bbifyMessage($Announcement['Msg']); ?>
			</td>
		</tr><?php
	} ?>

</table>
<br />

<div class="buttonA">
	<a class="buttonA" href="<?php echo $ContinueHREF; ?>">&nbsp;Continue&nbsp;</a>
</div>
