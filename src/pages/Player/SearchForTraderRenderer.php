<?php declare(strict_types=1);

namespace Smr\Pages\Player;

class SearchForTraderRenderer {

	public static function render(string $TraderSearchHREF, bool $EmptyResult): void {
		if ($EmptyResult) { ?>
			<p><span class="bold red">No trader found that matches your search!</span></p><?php
		} ?>

		<form method="POST" action="<?php echo $TraderSearchHREF; ?>">
		<p>
			Player name:<br />
			<input type="text" name="player_name" style="width:150px">&nbsp;<?php echo create_submit_display('Search'); ?>

			<br /><br /><br />

			Player ID:<br />
			<input type="number" name="player_id" style="width:50px">&nbsp;<?php echo create_submit_display('Search'); ?>

		</p>
		</form>

		<?php
	}

}
