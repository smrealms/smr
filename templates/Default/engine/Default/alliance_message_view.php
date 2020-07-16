<?php
if (isset($PrevThread) || isset($NextThread)) { ?>
	<h2>Switch Topic</h2><br />
	<table class="nobord fullwidth">
		<tr><?php
		if (isset($PrevThread)) { ?>
			<td>
				<a href="<?php echo $PrevThread['Href']; ?>"><img src="images/album/rew.jpg" alt="Previous" title="Previous"></a>
				&nbsp;&nbsp;<?php echo $PrevThread['Topic']; ?>
			</td><?php
		} else {
			?><td>&nbsp;</td><?php
		}
		if (isset($NextThread)) { ?>
			<td class="right">
			<?php echo $NextThread['Topic']; ?>&nbsp;&nbsp;
			<a href="<?php echo $NextThread['Href']; ?>"><img src="images/album/fwd.jpg" alt="Next" title="Next"></a>
			</td><?php
		} else {
			?><td>&nbsp;</td><?php
		} ?>
		</tr>
	</table><br /><?php
} ?>

<h2>Messages</h2>
<div><?php
	if ($Thread['AllianceEyesOnly']) { ?><br />Note: This topic is for alliance eyes only.<?php } ?>
	<br />
	<table class="standard inset centered">
		<tr>
			<th>Author</th>
			<th>Message</th>
			<th>Time</th><?php
			if ($Thread['CanDelete']) {
				?><th></th><?php
			} ?>
		</tr><?php
		foreach ($Thread['Replies'] as $Reply) { ?>
			<tr>
				<td class="shrink noWrap top"><?php echo $Reply['Sender']; ?></td>
				<td><?php echo bbifyMessage($Reply['Message']); ?></td>
				<td class="shrink noWrap top"><?php echo date(DATE_FULL_SHORT, $Reply['SendTime']); ?></td><?php
				if ($Thread['CanDelete']) {
					?><td class="shrink noWrap top"><a href="<?php echo $Reply['DeleteHref']; ?>"><img src="images/silk/cross.png" width="16" height="16" alt="Delete" title="Delete Post"/></a></td><?php
				} ?>
			</tr><?php
		} ?>
	</table>
</div><?php
if (isset($Thread['CreateThreadReplyFormHref'])) { ?>
	<br /><h2>Create Reply</h2><br /><?php
	if (isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
	<form class="standard" id="CreateThreadReplyForm" method="POST" action="<?php echo $Thread['CreateThreadReplyFormHref']; ?>">
		<table class="nobord nohpad">
			<tr>
				<td class="top">Body:&nbsp;</td>
				<td><textarea spellcheck="true" name="body" required><?php if (isset($Preview)) { echo $Preview; } ?></textarea></td>
			</tr>
		</table><br />
		<input type="submit" name="action" value="Create Reply">&nbsp;<input type="submit" name="action" value="Preview Reply" class="InputFields" />
	</form><?php
}
?>
