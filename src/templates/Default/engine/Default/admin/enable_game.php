<?php declare(strict_types=1);

/**
 * @var ?string $ProcessingMsg
 * @var array<int, string> $DisabledGames
 */

// This var is passed by the processing file if we enabled a game
if (isset($ProcessingMsg)) {
	echo $ProcessingMsg;
}

if (count($DisabledGames) === 0) { ?>
	<p>All games are already enabled!</p><?php
} else { ?>

	<p>Select the game you would like to enable.<br />
	This will make it visible to all players, and will create the Newbie Help Alliance.</p>

	<form method="POST" action="<?php echo $EnableGameHREF; ?>">
		<table class="standard">
			<tr>
				<td class="center">
					<select name="game_id"><?php
						foreach ($DisabledGames as $id => $name) {
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
