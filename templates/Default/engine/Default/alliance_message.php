<?php
if (count($Threads) > 0) { ?>
	<div align="center">
	<table class="standard inset">
		<tr>
			<th>Topic</th>
			<th>Author</th>
			<th>Replies</th>
			<th>Last Reply</th>
		</tr><?php
		foreach($Threads as $Thread) { ?>
			<tr>
				<td><?php
					if ($Thread['Unread']) {
						?><b><?php
					}
					?><a href="<?php echo $Thread['ViewHref']; ?>"><?php echo $Thread['Topic']; ?></a><?php
					if ($Thread['Unread']) {
						?></b><?php
					} ?>
				</td>
				<td class="shrink noWrap"><?php
					echo $Thread['Sender'];
					if($Thread['CanDelete']) {
						?><br /><small><a href="<?php echo $Thread['DeleteHref']; ?>">Delete Thread!</a></small><?php
					} ?>
				</td>
				<td class="shrink center"><?php echo $Thread['Replies']; ?></td>
				<td class="shrink noWrap"><?php echo date(DATE_FULL_SHORT, $Thread['SendTime']); ?>
				</td>
			</tr><?php
		} ?>
		</table>
	</div><br /><?php
}

if (isset($CreateNewThreadFormHref)) { ?>
	<h2>Create Thread</h2><br /><?php
	if(isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
	<form class="standard" id="CreateNewThreadForm" method="POST" action="<?php echo $CreateNewThreadFormHref; ?>">
	<table class="standardnobord nohpad">
		<tr>
			<td class="top">Topic:&nbsp;</td>
			<td class="mb"><input type="text" name="topic" size="30" value="<?php if(isset($Topic)) { echo htmlspecialchars($Topic); } ?>"></td>
			<td style="text-align:left;">For Alliance Eyes Only:<input id="InputFields" name="allEyesOnly" type="checkbox"<?php if(isset($AllianceEyesOnly) && $AllianceEyesOnly) { ?>checked="checked" <?php } ?>></td>
		</tr>
		<tr>
			<td class="top">Body:&nbsp;</td>
			<td colspan="2"><textarea name="body"><?php if(isset($Preview)) { echo $Preview; } ?></textarea></td>
		</tr>
	</table><br />
	<input class="submit" type="submit" name="action" value="New Thread">&nbsp;<input type="submit" name="action" value="Preview Thread" id="InputFields" />
	</form><?php
}
?>