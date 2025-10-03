<?php declare(strict_types=1);

/**
 * @var Smr\Account $ThisAccount
 * @var array{AllianceEyesOnly: bool, CanDelete: bool, Replies: array<int, array{Sender: string, Message: string, SendTime: int, DeleteHref?: string}>, CreateThreadReplyFormHref?: string} $Thread
 */

if (isset($PrevThread) || isset($NextThread)) { ?>
	<h2>Switch Topic</h2><br />
	<table class="nobord fullwidth">
		<tr><?php
		if (isset($PrevThread)) { ?>
			<td>
				<a href="<?php echo $PrevThread['Href']; ?>"><img src="images/album/rew.jpg" alt="Previous" title="Previous"></a>
				&nbsp;&nbsp;<?php echo htmlentities($PrevThread['Topic']); ?>
			</td><?php
		} else {
			?><td>&nbsp;</td><?php
		}
		if (isset($NextThread)) { ?>
			<td class="right">
			<?php echo htmlentities($NextThread['Topic']); ?>&nbsp;&nbsp;
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
				<td><?php echo bbify($Reply['Message']); ?></td>
				<td class="shrink noWrap top"><?php echo date($ThisAccount->getDateTimeFormat(), $Reply['SendTime']); ?></td><?php
				if (isset($Reply['DeleteHref'])) {
					?><td class="shrink noWrap top"><a href="<?php echo $Reply['DeleteHref']; ?>"><img src="images/silk/cross.png" width="16" height="16" alt="Delete" title="Delete Post"/></a></td><?php
				} ?>
			</tr><?php
		} ?>
	</table>
</div><?php
if (isset($Thread['CreateThreadReplyFormHref'])) { ?>
	<br /><h2>Create Reply</h2><br /><?php
	if (isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbify($Preview); ?></td></tr></table><?php } ?>
	<form class="standard" id="CreateThreadReplyForm" method="POST" action="<?php echo $Thread['CreateThreadReplyFormHref']; ?>">
		<table class="nobord nohpad">
			<tr>
				<td class="top">Body:&nbsp;</td>
				<td><textarea spellcheck="true" name="body" required><?php if (isset($Preview)) { echo $Preview; } ?></textarea></td>
			</tr>
		</table><br />
		<?php echo create_submit('action', 'Create Reply'); ?>&nbsp;<?php echo create_submit('action', 'Preview Reply'); ?>
	</form><?php
}
