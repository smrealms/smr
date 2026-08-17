<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class GameDeleteRenderer {

/** @param array<array{game_id: int, display: string}> $Games */
public static function render(string $ConfirmHREF, array $Games): void {
?>
<p>
	What game do you want to delete?<br />
	<small>Note: games cannot be deleted once they are enabled.</small>
</p>

<form method="POST" action="<?php echo $ConfirmHREF; ?>">
	<select required name="delete_game_id">
		<option value="" disabled selected>[Select the game]</option><?php
		foreach ($Games as $Game) { ?>
			<option value="<?php echo $Game['game_id']; ?>"><?php echo $Game['display']; ?></option><?php
		} ?>
	</select>
	&nbsp;&nbsp;
	<?php echo create_submit_display('Delete'); ?>
</form>
<?php }

}
