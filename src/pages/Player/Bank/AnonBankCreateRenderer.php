<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

class AnonBankCreateRenderer {

	public static function render(string $CreateHREF): void {
		?>
		Please enter the desired password for your new account.<br /><br />
		<form method="POST" action="<?php echo $CreateHREF; ?>">
			<table cellspacing="0" cellpadding="0" class="nobord nohpad">
				<tr>
					<td class="top">Password:&nbsp;</td>
					<td><input name="password" required size="30" maxlength="20"></td>
				</tr>
			</table>
			<br />
			<?php echo create_submit_display('Create Account'); ?>
		</form>
		<?php
	}

}
