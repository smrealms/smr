<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class ReportedMessageReplyRenderer {

	public static function render(
		ReportedMessageReplyProcessor $NotifyReplyFormPage,
		string $Offender,
		string $Offended,
		?string $PreviewOffender,
		?int $OffenderBanPoints,
		?string $PreviewOffended,
		?int $OffendedBanPoints,
	): void {
		?>
		Leave a message box blank to not reply to that player.<br />
		<br />
		<form name="NotifyReplyForm" method="POST" action="<?php echo $NotifyReplyFormPage->href(); ?>">
			<?php if ($PreviewOffender !== null) { ?><table class="standard"><tr><td><?php echo bbify($PreviewOffender); ?></td></tr></table><?php } ?>
			<b>From: </b><span class="admin">Administrator</span><br />
			<b>To Offender: </b><?php echo $Offender; ?><br />
			<input type="number" value="<?php if ($OffenderBanPoints !== null) { echo $OffenderBanPoints; } else { ?>0<?php } ?>" name="offenderBanPoints" size="4" /> Points<br />
			<textarea spellcheck="true" name="offenderReply"><?php if ($PreviewOffender !== null) { echo $PreviewOffender; } ?></textarea><br /><br />

			<?php if ($PreviewOffended !== null) { ?><table class="standard"><tr><td><?php echo bbify($PreviewOffended); ?></td></tr></table><?php } ?>
			<b>From: </b><span class="admin">Administrator</span><br />
			<b>To Offended: </b><?php echo $Offended; ?><br />
			<input type="number" value="<?php if ($OffendedBanPoints !== null) { echo $OffendedBanPoints; } else { ?>0<?php } ?>" name="offendedBanPoints" size="4" /> Points<br />
			<textarea spellcheck="true" name="offendedReply"><?php if ($PreviewOffended !== null) { echo $PreviewOffended; } ?></textarea><br /><br />

			<?php echo $NotifyReplyFormPage->actionSend->html(); ?>&nbsp;<?php echo $NotifyReplyFormPage->actionPreview->html(); ?>
		</form>

		<?php
	}

}
