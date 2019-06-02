<?php

if (empty($ActiveGames)) {
	echo "<p>There are no active games at this time!</p>";
} else { ?>

	<p>Specify the Game and Player ID to assign or remove a Galactic Post editor.</p>

	Select Game:&nbsp;
	<form class="standard" id="SelectGameForm" method="POST" action="">
		<select name="game_id" onchange="this.form.submit()"><?php
			foreach ($ActiveGames as $Game) {
				$id = $Game['game_id'];
				$name = $Game['game_name'];
				$selected = ($SelectedGame == $id ? 'selected="selected"' : '');
				echo "<option value='$id' $selected>$name ($id)</option>";
			} ?>
		</select>
	</form><br />

	Player ID:&nbsp;
	<form method="POST" action="<?php echo $PostEditorHREF; ?>">
		<input type="number" name="player_id" class="InputFields center">
		<br />
		<input type="submit" name="submit" value="Assign">&nbsp;
		<input type="submit" name="submit" value="Remove">
	</form>
	<?php

	// This var is passed by the processing file if we enabled a game
	if (!empty($ProcessingMsg)) {
		echo '<br />' . $ProcessingMsg;
	} ?>
	<br /><br />

	<?php
	if (empty($CurrentEditors)) {
		echo "No current editors for this game!";
	} else { ?>
		Current Editors:
		<ul><?php
		foreach ($CurrentEditors as $Editor) {
			echo "<li>$Editor</li>";
		} ?>
		</ul><?php
	}

}

?>
