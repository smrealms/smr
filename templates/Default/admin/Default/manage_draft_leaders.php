<?php

if (empty($ActiveGames)) {
	echo "<p>There are no active Draft games at this time!</p>";
} else { ?>

	<p>Specify the Game and Player ID to assign or remove a Draft Leader.</p>

	Select Game:&nbsp;
	<form class="standard" id="SelectGameForm" method="POST" action="">
		<select name="selected_game_id" onchange="this.form.submit()"><?php
			foreach ($ActiveGames as $Game) {
				$id = $Game['game_id'];
				$name = $Game['game_name'];
				$selected = ($SelectedGame == $id ? 'selected="selected"' : '');
				echo "<option value='$id' $selected>$name ($id)</option>";
			} ?>
		</select>
	</form><br />

	Player ID:&nbsp;
	<form method="POST" action="<?php echo $ProcessingHREF; ?>">
		<input type="number" name="player_id" class="InputFields center">
		<br />
		<input type="submit" name="submit" value="Assign">&nbsp;
		<input type="submit" name="submit" value="Remove">
	</form>
	<?php

	// This var is passed by the processing file if there was an error
	if (!empty($ProcessingMsg)) {
		echo "<p>$ProcessingMsg</p>";
	}

	if (empty($CurrentLeaders)) {
		echo "<p>No current Draft Leaders for this game!</p>";
	} else { ?>
		<br />
		Current Draft Leaders:
		<ul><?php
		foreach ($CurrentLeaders as $Leader) {
			echo "<li>$Leader</li>";
		} ?>
		</ul><?php
	}

}

?>
