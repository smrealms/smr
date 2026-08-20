<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class AnnouncementCreateRenderer {

	public static function render(AnnouncementCreateProcessor $AnnouncementCreateForm, ?string $Preview): void {
		?>
		Announcements are displayed to all users the next time they log in.<br />
		You may use BBCode in your message, but not HTML.<br /><br />
		<?php if ($Preview !== null) { ?><table class="standard"><tr><td><?php echo bbify($Preview); ?></td></tr></table><br /><?php } ?>
		<form name="AnnouncementCreateForm" method="POST" action="<?php echo $AnnouncementCreateForm->href(); ?>">
			<textarea required spellcheck="true" name="message"><?php if ($Preview !== null) { echo $Preview; } ?></textarea><br />
			<?php echo $AnnouncementCreateForm->actionCreate->html('Create announcement'); ?>&nbsp;
			<?php echo $AnnouncementCreateForm->actionPreview->html('Preview announcement'); ?>
		</form>
		<?php
	}

}
