Leave a message box blank to not reply to that player.<br />
<br />
<form name="NotifyReplyForm" method="POST" action="<?php echo $NotifyReplyFormHref; ?>">
	<?php if (isset($PreviewOffender)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($PreviewOffender); ?></td></tr></table><?php } ?>
	<b>From: </b><span class="admin">Administrator</span><br />
	<b>To: </b><?php if (is_object($Offender)) { echo $Offender->getDisplayName(); ?> a.k.a <?php echo $OffenderAccount->getLogin(); } ?> (Offender)<br />
	<input type="number" value="<?php if (isset($OffenderBanPoints)) { echo htmlspecialchars($OffenderBanPoints); } else { ?>0<?php } ?>" name="offenderBanPoints" size="4" /> Points<br />
	<textarea spellcheck="true" name="offenderReply"><?php if (isset($PreviewOffender)) { echo $PreviewOffender; } ?></textarea><br /><br />

	<?php if (isset($PreviewOffended)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($PreviewOffended); ?></td></tr></table><?php } ?>
	<b>From: </b><span class="admin">Administrator</span><br />
	<b>To: </b><?php if (is_object($Offended)) { echo $Offended->getDisplayName(); ?> a.k.a <?php echo $OffendedAccount->getLogin(); } ?> (Offended)<br />
	<input type="number" value="<?php if (isset($OffendedBanPoints)) { echo htmlspecialchars($OffendedBanPoints); } else { ?>0<?php } ?>" name="offendedBanPoints" size="4" /> Points<br />
	<textarea spellcheck="true" name="offendedReply"><?php if (isset($PreviewOffended)) { echo $PreviewOffended; } ?></textarea><br /><br />

	<input type="submit" name="action" value="Send messages" />&nbsp;<input type="submit" name="action" value="Preview messages" />
</form>
