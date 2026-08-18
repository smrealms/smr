<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class AdminMessageSendRenderer {

	/**
	 * @param ?list<array{AccountID: int, Name: string}> $GamePlayers
	 */
	public static function render(
		AdminMessageSendProcessor $AdminMessageSendForm,
		?int $MessageGameID,
		?float $ExpireTime,
		?array $GamePlayers,
		int $SelectedAccountID,
		?string $Preview,
		string $BackHREF,
	): void {
		if (isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbify($Preview, $MessageGameID); ?></td></tr></table><?php } ?>
		<form name="AdminMessageSendForm" method="POST" action="<?php echo $AdminMessageSendForm->href(); ?>">
			<p>
			<b>From: </b><span class="admin">Administrator</span><br />
			<b>To: </b><?php
				if ($GamePlayers !== null) { ?>
					<select name="account_id" required size="1"><?php
						foreach ($GamePlayers as $GamePlayer) {
							?><option <?php if ($SelectedAccountID === $GamePlayer['AccountID']) { echo 'selected'; } ?> value="<?php echo $GamePlayer['AccountID']; ?>"><?php echo $GamePlayer['Name']; ?></option><?php
						} ?>
					</select><br /><br /><?php
				} else { ?>
					All Players (All Games)<?php
				} ?>
			</p>
			<textarea required spellcheck="true" name="message"><?php if (isset($Preview)) { echo $Preview; } ?></textarea><br />
			Hours Till Expire: <input required type="number" step="0.01" name="expire" value="<?php echo $ExpireTime; ?>" min="0" size="2"> (0 = never expire)<br />
			<br />
			<?php echo $AdminMessageSendForm->actionSend->html('Send message'); ?>&nbsp;<?php echo $AdminMessageSendForm->actionPreview->html('Preview message'); ?>
		</form>
		<br /><br />
		<a href="<?php echo $BackHREF; ?>">&lt;&lt; Back</a>

		<?php
	}

}
