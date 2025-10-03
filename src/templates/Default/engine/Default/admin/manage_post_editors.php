<?php declare(strict_types=1);

/**
 * @var array<array{game_name: string, game_id: int}> $ActiveGames
 * @var ?array<string> $CurrentEditors
 */

if (count($ActiveGames) === 0) {
	echo '<p>There are no active games at this time!</p>';
} else { ?>

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
	<form method="POST" action="<?php echo $PostEditorHREF; ?>">
		<input type="number" name="player_id" class="center">
		<br />
		<?php echo create_submit('submit', 'Assign'); ?>&nbsp;
		<?php echo create_submit('submit', 'Remove'); ?>
	</form>
	<?php

	// This var is passed by the processing file if we enabled a game
	if (isset($ProcessingMsg)) {
		echo '<br />' . $ProcessingMsg;
	} ?>
	<br /><br />

	<?php
	if (!isset($CurrentEditors) || count($CurrentEditors) === 0) {
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
