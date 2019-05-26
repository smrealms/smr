<table class="standard">
	<tr>
		<th>Time</th>
		<th>News</th>
	</tr>
	<?php
	foreach ($NewsItems as $NewsItem) { ?>
		<tr>
			<td class="center"><?php echo $NewsItem['Date']; ?></td>
			<td><?php echo $NewsItem['Message']; ?></td>
		</tr><?php
	} ?>
</table>
