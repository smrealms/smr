<?php
if (isset($Links)) { ?>
	<table class="center standard">
		<tr><th>Categories</th></td><?php
		foreach ($Links as $link) { ?>
			<tr><td class="center"><?php echo $link; ?></td></tr><?php
		} ?>
	</table><?php
}
else { ?>
	<div class="center"><a href="<?php echo $BackHREF; ?>">&lt;&lt;Back</a></div>

	<?php
	if (!empty($Rankings)) { ?>
		<table class="shrink center standard">
			<tr>
				<th>Rank</th>
				<th>Player</th>
				<th><?php echo $StatName; ?></th>
			</tr><?php
			foreach ($Rankings as $index => $ranking) { ?>
				<tr <?php echo $ranking['bold']; ?>>
					<td><?php echo $index + 1; ?></td>
					<td class="noWrap"><?php echo $ranking['name']; ?></td>
					<td><?php echo number_format($ranking['stat']); ?></td>
				</tr><?php
			} ?>
		</table><?php
	} else { ?>
		<p class="center">We apologize, but this stat does not exist for this game!</p><?php
	}
} ?>
