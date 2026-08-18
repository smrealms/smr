<?php declare(strict_types=1);

namespace Smr\Pages\Account;

class ErrorDisplayRenderer {

	public static function render(string $Message): void {
		?>
		<p class="big bold"><?php echo $Message; ?></p>
		<br /><br />
		<p>If the error was caused by something you entered, press back and try again.</p>
		<p>If it was a DB Error, press back and try again, or logoff and log back on.</p>
		<p>If the error was unrecognizable, please notify the administrators.</p>
		<?php
	}

}
