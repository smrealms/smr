<?php

// This var is passed by the processing file if we enabled a game
if (!empty($ProcessingMsg)) {
	echo $ProcessingMsg;
}

if (empty($DisabledGames)) { ?>
	<p>All games are already enabled!</p><?php
} else { ?>

	<p>Select the game you would like to enable.<br />
	This will make it visible to all players.</p>

	<form method="POST" action="<?php echo $EnableGameHREF; ?>">
		<table class="standard">
			<tr>
				<td class="center">
					<select name="game_id"><?php
						foreach($DisabledGames as $Game) {
							$id = $Game['game_id'];
							$name = $Game['game_name'];
							echo "<option value=\"$id\">$name ($id)</option>";
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="center"><input type="submit" value="Enable Game"></td>
			</tr>
		</table>
	</form> <?php
}

?>
