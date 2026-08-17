<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class ManagePostEditorsRenderer {

	public static function renderEmpty(): void {
			echo '<p>There are no active games at this time!</p>';
	}

	/**
	 * @param array<array{game_name: string, game_id: int}> $ActiveGames
	 * @param array<string> $CurrentEditors
	 */
	public static function render(
		string $SelectGameHREF,
		array $ActiveGames,
		int $SelectedGame,
		array $CurrentEditors,
		?string $ProcessingMsg,
		ManagePostEditorsProcessor $PostEditorPage,
	): void {
		?>
		<p>Specify the Game and Player ID to assign or remove a Galactic Post editor.</p>

		Select Game:&nbsp;
		<form class="standard" id="SelectGameForm" method="POST" action="<?php echo $SelectGameHREF; ?>">
			<select name="selected_game_id" onchange="this.form.submit()"><?php
				foreach ($ActiveGames as $Game) {
					$id = $Game['game_id'];
					$name = $Game['game_name'];
					$selected = ($SelectedGame === $id ? 'selected="selected"' : '');
					echo "<option value='$id' $selected>$name ($id)</option>";
				} ?>
			</select>
		</form><br />

		Player ID:&nbsp;
		<form method="POST" action="<?php echo $PostEditorPage->href(); ?>">
			<input type="number" name="player_id" class="center">
			<br /><br />
			<?php echo $PostEditorPage->actionAssign->html(); ?>&nbsp;
			<?php echo $PostEditorPage->actionRemove->html(); ?>
		</form>
		<?php

		// This var is passed by the processing file if we enabled a game
		if ($ProcessingMsg !== null) {
			echo '<br />' . $ProcessingMsg;
		} ?>
		<br /><br />

		<?php
		if (count($CurrentEditors) === 0) {
			echo 'No current editors for this game!';
		} else { ?>
			Current Editors:
			<ul><?php
			foreach ($CurrentEditors as $Editor) {
				echo "<li>$Editor</li>";
			} ?>
			</ul><?php
		}

	}

}
