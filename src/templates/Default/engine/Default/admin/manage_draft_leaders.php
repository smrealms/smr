<?php declare(strict_types=1);

/**
 * @var array<array{game_name: string, game_id: int}> $ActiveGames
 */

if (count($ActiveGames) === 0) {
	echo '<p>There are no active Draft games at this time!</p>';
} else { ?>

	<p>Specify the Game and Player ID to assign or remove a Draft Leader.</p>

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

	<form method="POST" action="<?php echo $ProcessingHREF; ?>">
		<table>
			<tr>
				<td>Player ID:</td>
				<td>Home Sector ID (optional):</td>
			</tr>
			<tr>
				<td><input required type="number" name="player_id" class="center"></td>
				<td><input type="number" name="home_sector_id" class="center"></td>
			</tr>
			<tr>
				<td colspan=2>
					<?php echo create_submit('submit', 'Assign'); ?>&nbsp;
					<?php echo create_submit('submit', 'Remove'); ?>
				</td>
			</tr>
		</table>
	</form>
	<?php

	// This var is passed by the processing file if there was an error
	if (isset($ProcessingMsg)) {
		echo "<p>$ProcessingMsg</p>";
	}

	if (!isset($CurrentLeaders) || count($CurrentLeaders) === 0) {
		echo '<p>No current Draft Leaders for this game!</p>';
	} else { ?>
		<br />
		Current Draft Leaders:
		<ul><?php
		foreach ($CurrentLeaders as $Leader) { ?>
			<li><?php echo $Leader['Name']; ?><br />Home Sector: <?php echo $Leader['HomeSectorID']; ?></li><?php
		} ?>
		</ul><?php
	}

}
