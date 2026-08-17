<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

class BountyPlaceConfirmRenderer {

	public static function render(string $Amount, string $SmrCredits, string $BountyPlayer, string $ConfirmHREF, string $CancelHREF): void {
		?>
		<p>Are you sure you want to place a <span class="creds"><?php echo $Amount; ?></span>
		credit and <span class="yellow"><?php echo $SmrCredits; ?></span>
		SMR credit bounty on <?php echo $BountyPlayer; ?>?</p>

		<?php echo create_submit_link($ConfirmHREF, 'Yes'); ?>
		&nbsp;&nbsp;
		<?php echo create_submit_link($CancelHREF, 'No');
	}

}
