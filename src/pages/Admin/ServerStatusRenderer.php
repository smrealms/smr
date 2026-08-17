<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class ServerStatusRenderer {

	public static function render(ServerStatusProcessor $ProcessingPage, bool $ServerIsOpen): void {
		?>

		<form method="POST" action="<?php echo $ProcessingPage->href(); ?>">
		<?php
		if ($ServerIsOpen) { ?>
			If you wish to close Space Merchant Realms, please enter a reason for the closure.
			This will be displayed when players attempt to log in during the closure.<br /><br />
			<b>Reason: </b>
			<input spellcheck="true" type="text" name="close_reason" maxlength="255" size="80"><br /><br />
			<b>NOTE:</b> Closing the server will kick all players and disable general logins.
			Only admins with permission to reopen the game will be allowed to log in while closed.<br /><br />
			<?php echo $ProcessingPage->actionClose->html();
		} else { ?>
			Do you want to reopen Space Merchant Realms?<br /><br />
			<?php echo $ProcessingPage->actionOpen->html();
		} ?>
		</form>
		<?php
	}

}
