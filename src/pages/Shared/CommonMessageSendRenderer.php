<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Pages\Player\MessageSendProcessor;
use Smr\Player;

class CommonMessageSendRenderer {

	public static function render(
		Player $ThisPlayer,
		string $Receiver,
		MessageSendProcessor $MessageSendPage,
		?string $Preview,
	): void {
		if (isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbify($Preview); ?></td></tr></table><?php } ?>
		<form name="MessageSendForm" method="POST" action="<?php echo $MessageSendPage->href(); ?>">
			<p>
				<b>From: </b><?php echo $ThisPlayer->getDisplayName(); ?><br />
				<b>To: </b><?php echo $Receiver; ?>
			</p>
			<textarea spellcheck="true" name="message" required><?php if (isset($Preview)) { echo $Preview; } ?></textarea><br />
			<br />
			<?php echo $MessageSendPage->actionSend->html('Send message'); ?>&nbsp;<?php echo $MessageSendPage->actionPreview->html('Preview message'); ?>
		</form>

		<?php
	}

}
