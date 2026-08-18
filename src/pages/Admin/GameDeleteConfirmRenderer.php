<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Game;

class GameDeleteConfirmRenderer {

	public static function render(string $CancelHREF, string $ConfirmHREF, Game $Game): void {
		?>
		Are you sure you want to delete the game: <i><?php echo $Game->getDisplayName(); ?></i>?
		<br /><br />

		<?php echo create_submit_link($ConfirmHREF, 'Yes'); ?>
		&nbsp;&nbsp;
		<?php echo create_submit_link($CancelHREF, 'No');

	}

}
