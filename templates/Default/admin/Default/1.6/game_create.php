
<h1>Edit Existing Games</h1>
<?php
if (count($EditGames) == 0) { ?>
	There are no games for you to edit.<?php
	if (!$CanEditStartedGames) { ?>
		<br />NOTE: You do not have permission to edit games that have already started.<?php
	}
} else { ?>
	<form method="POST" action="<?php echo $EditGameHREF; ?>">
		<table class="standard">
			<tr>
				<td class="right">
					<select name="game_id"><?php
						foreach($EditGames as $EditGame) {
							?><option value="<?php echo $EditGame->getGameID(); ?>"><?php echo $EditGame->getDisplayName(); ?></option><?php
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="center"><input type="submit" value="Edit" name="Edit"></td>
			</tr>
		</table>
	</form><?php
} ?>

<br /><br />

<h1>Create New Game</h1>
<form method="POST" action="<?php echo $CreateGalaxiesHREF; ?>">
	<table class="standard">
	<tr>
		<td class="right">Game Name</td>
		<td><input type="text" size="32" name="game_name" value="<?php echo $Game['name']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Game Description</td>
		<td><textarea spellcheck="true" name="desc"><?php echo $Game['description']; ?></textarea></td>
	</tr>
	<tr>
		<td class="right">Game Speed</td>
		<td><input type="number" size="6" name="game_speed" step=".05" value="<?php echo $Game['speed']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Max Turns</td>
		<td><input type="number" size="6" name="max_turns" step="5" value="<?php echo $Game['maxTurns']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Starting Turn Hours</td>
		<td><input type="number" size="6" name="start_turns" value="<?php echo $Game['startTurnHours']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Max Players</td>
		<td><input type="number" size="6" name="max_players" value="<?php echo $Game['maxPlayers']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Number of Galaxies</td>
		<td><input type="number" size="5" name="num_gals" value="<?php echo $Game['numGalaxies']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Start Date (DD/MM/YYYY)</td>
		<td><input type="text" size="20" name="game_start" value="<?php echo $Game['startDate']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Turns Start Date (DD/MM/YYYY)</td>
		<td><input type="text" size="20" name="game_start_turns" value="<?php echo $Game['startTurnsDate']; ?>"> Leave blank to use Start Date</td>
	</tr>
	<tr>
		<td class="right">End Date (DD/MM/YYYY)</td>
		<td><input type="text" size="20" name="game_end" value="<?php echo $Game['endDate']; ?>"></td></tr>
	<tr>
		<td class="right">SMR Credits Required</td>
		<td><input type="number" size="5" name="creds_needed" value="<?php echo $Game['smrCredits']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Game Type</td>
		<td>
			<select name="game_type" id="InputFields"><?php
			foreach($GameTypes as $GameTypeID => $GameType) {
				?><option value="<?php echo $GameTypeID; ?>"><?php echo $GameType; ?></option><?php
			} ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="right">Alliance Max Players</td>
		<td><input type="number" size="6" name="alliance_max_players" value="<?php echo $Game['allianceMax']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Alliance Max Vets</td>
		<td><input type="number" size="6" name="alliance_max_vets" value="<?php echo $Game['allianceMaxVets']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Starting Credits</td>
		<td><input type="number" size="6" name="starting_credits" value="<?php echo $Game['startCredits']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Ignore Stats</td>
		<td>
			Yes: <input type="radio" name="ignore_stats" id="InputFields" value="Yes" <?php if ($Game['ignoreStats']) { echo "checked"; } ?> /><br />
			No: <input type="radio" name="ignore_stats" id="InputFields" value="No" <?php if (!$Game['ignoreStats']) { echo "checked"; } ?> /><br />
		</td>
	</tr>
	<tr>
		<td class="center" colspan="2"><input type="submit" value="Create Game" name="submit"></td>
	</tr>
	</table>
</form>
