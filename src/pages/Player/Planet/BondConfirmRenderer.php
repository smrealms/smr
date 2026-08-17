<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

class BondConfirmRenderer {

	public static function render(string $CancelHREF, string $ConfirmHREF, string $BondDuration): void {
		?>
		<h2>Planetary Bond Confirmation</h2>

		<p>All credits on the planet at the time of confirmation, along with any
		credits currently bonded (and any partial interest they may have accrued),
		will be added to a new bond. You will not be able to access these funds until
		the bond matures in <?php echo $BondDuration; ?>.</p>

		<p>Please confirm to proceed.</p>

		<table>
			<tr>
				<td><?php echo create_submit_link($ConfirmHREF, 'Confirm'); ?></td>
				<td><?php echo create_submit_link($CancelHREF, 'Cancel'); ?></td>
			</tr>
		</table>
		<?php
	}

}
