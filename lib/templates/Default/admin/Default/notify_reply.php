Leave a message box blank to not reply to that player.<br />
<br />
<form name="NotifyReplyForm" method="POST" action="<?php echo $NotifyReplyFormHref; ?>">
	<?php if(isset($PreviewOffender)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($PreviewOffender); ?></td></tr></table><?php } ?>
	<b>From: </b><span class="admin">Administrator</span><br />
	<b>To: </b><?php if(is_object($Offender)) { echo $Offender->getPlayerName(); ?> a.k.a <?php echo $OffenderAccount->getLogin(); } ?> (Offender)<br />
	<input type="text" value="<?php if(isset($OffenderBanPoints)) { echo htmlspecialchars($OffenderBanPoints); } else { ?>0<?php } ?>" name="offenderBanPoints" size="4" /> Points<br />
	<textarea name="offenderReply" id="InputFields"><?php if(isset($PreviewOffender)) { echo $PreviewOffender; } ?></textarea><br /><br />

	<?php if(isset($PreviewOffended)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($PreviewOffended); ?></td></tr></table><?php } ?>
	<b>From: </b><span class="admin">Administrator</span><br />
	<b>To: </b><?php if(is_object($Offended)) { echo $Offended->getPlayerName(); ?> a.k.a <?php echo $OffendedAccount->getLogin(); } ?> (Offended)<br />
	<input type="text" value="<?php if(isset($OffendedBanPoints)) { echo htmlspecialchars($OffendedBanPoints); } else { ?>0<?php } ?>" name="offendedBanPoints" size="4" /> Points<br />
	<textarea name="offendedReply" id="InputFields"><?php if(isset($PreviewOffended)) { echo $PreviewOffended; } ?></textarea><br /><br />

	<input type="submit" name="action" value="Send messages" id="InputFields" />&nbsp;<input type="submit" name="action" value="Preview messages" id="InputFields" />
</form>