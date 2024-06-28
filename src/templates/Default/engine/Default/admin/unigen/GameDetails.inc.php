<?php declare(strict_types=1);

use Smr\Game;

?>
<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	<table class="standard">
	<tr>
		<td class="right">Game Name</td>
		<td><input required type="text" size="32" name="game_name" value="<?php echo $Game['name']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Game Description</td>
		<td><textarea spellcheck="true" name="desc"><?php echo $Game['description']; ?></textarea></td>
	</tr>
	<tr>
		<td class="right">Game Speed</td>
		<td><input required type="number" size="6" name="game_speed" step=".05" value="<?php echo $Game['speed']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Max Turns</td>
		<td><input required type="number" size="6" name="max_turns" step="5" value="<?php echo $Game['maxTurns']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Starting Turn Hours</td>
		<td><input required type="number" size="6" name="start_turns" value="<?php echo $Game['startTurnHours']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Max Players</td>
		<td><input required type="number" size="6" name="max_players" value="<?php echo $Game['maxPlayers']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Join Date</td>
		<td><input required type="date" name="game_join" value="<?php echo $Game['joinDate']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Start Date</td>
		<td><input type="date" name="game_start" value="<?php echo $Game['startDate']; ?>"> Leave blank to use Join Date</td>
	</tr>
	<tr>
		<td class="right">End Date</td>
		<td><input required type="date" size="20" name="game_end" value="<?php echo $Game['endDate']; ?>"></td></tr>
	<tr>
		<td class="right">SMR Credits Required</td>
		<td><input type="number" size="5" name="creds_needed" value="<?php echo $Game['smrCredits']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Game Type</td>
		<td>
			<select name="game_type"><?php
			foreach (Game::GAME_TYPES as $GameTypeID => $GameType) {
				?><option value="<?php echo $GameTypeID; ?>" <?php if ($GameType === $Game['gameType']) echo 'selected'; ?>><?php echo $GameType; ?></option><?php
			} ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="right">Alliance Max Players</td>
		<td><input required type="number" size="6" name="alliance_max_players" value="<?php echo $Game['allianceMax']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Alliance Max Vets</td>
		<td><input required type="number" size="6" name="alliance_max_vets" value="<?php echo $Game['allianceMaxVets']; ?>"></td>
	</tr>
	<tr>
		<td class="right">Starting Credits</td>
		<td>
			<input required type="number" size="6" name="starting_credits" value="<?php echo $Game['startCredits']; ?>">
			Given at game start and on death
		</td>
	</tr>
	<tr>
		<td class="right">Ignore Stats</td>
		<td>
			Yes: <input type="radio" name="ignore_stats" value="Yes" <?php if ($Game['ignoreStats']) { echo 'checked'; } ?> /><br />
			No: <input type="radio" name="ignore_stats" value="No" <?php if (!$Game['ignoreStats']) { echo 'checked'; } ?> /><br />
		</td>
	</tr>
	<tr>
		<td class="right">Starting Relations</td>
		<td>
			<input required type="number" name="relations" min="<?php echo MIN_GLOBAL_RELATIONS; ?>" max="<?php echo MAX_GLOBAL_RELATIONS; ?>" value="<?php echo $Game['relations']; ?>">
			Only updated if game hasn't started yet
		</td>
	</tr>
	<tr>
		<td class="right">Permanent Port Destruction</td>
		<td>
			Yes: <input type="radio" name="destroy_ports" value="Yes" <?php if ($Game['destroyPorts']) { echo 'checked'; } ?> /><br />
			No: <input type="radio" name="destroy_ports" value="No" <?php if (!$Game['destroyPorts']) { echo 'checked'; } ?> /><br />
		</td>
	</tr>
	<tr>
		<td class="center" colspan="2"><input type="submit" value="<?php echo $SubmitValue; ?>" name="submit"></td>
	</tr>
	</table>
</form>
