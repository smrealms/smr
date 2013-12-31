<?php
//get information
$link=array();
$link['url'] = 'skeleton.php';
$link['body'] = '/1.6/universe_create_galaxies.php';
$link['nogid'] = TRUE;
$link['valid_for'] = -10;
create_echo_form($link);
?>
<form method="POST" action="<?php echo $CreateGalaxiesHREF; ?>">
	<table class="standard">
	<tr>
		<td class="right">Game Name</td>
		<td><input type="text" size="32" name="game_name" value=""></td>
	</tr>
	<tr>
		<td class="right">Game Description</td>
		<td><textarea spellcheck="true" name="desc"></textarea></td>
	</tr>
	<tr>
		<td class="right">Game Speed</td>
		<td><input type="number" size="6" name="game_speed" step=".05" value="1.5"></td>
	</tr>
	<tr>
		<td class="right">Max Turns</td>
		<td><input type="number" size="6" name="max_turns" step="5" value="<?php echo DEFAULT_MAX_TURNS; ?>"></td>
	</tr>
	<tr>
		<td class="right">Starting Turn Hours</td>
		<td><input type="number" size="6" name="start_turns" value="<?php echo DEFAULT_START_TURN_HOURS; ?>"></td>
	</tr>
	<tr>
		<td class="right">Max Players</td>
		<td><input type="number" size="6" name="max_players" value="5000"></td>
	</tr>
	<tr>
		<td class="right">Number of Galaxies</td>
		<td><input type="number" size="5" name="num_gals" value="12"></td>
	</tr>
	<tr>
		<td class="right">Start Date (DD/MM/YYYY)</td>
		<td><input type="text" size="32" name="game_start" value="<?php echo date('d/m/Y',TIME) ?>"></td>
	</tr>
	<tr>
		<td class="right">Turns Start Date (DD/MM/YYYY) - Leave blank if unsure</td>
		<td><input type="text" size="32" name="game_start_turns" value=""></td>
	</tr>
	<tr>
		<td class="right">End Date (DD/MM/YYYY)</td>
		<td><input type="text" size="32" name="game_end" value="<?php echo date('d/m/Y',$DefaultEnd) ?>"></td></tr>
	<tr>
		<td class="right">Credits Required</td>
		<td><input type="number" size="5" name="creds_needed" value="0"></td>
	</tr>
	<tr>
		<td class="right">Game Type</td>
		<td>
			<select name="game_type" id="InputFields"><?php
			foreach($GameTypes as $GameType) {
				?><option value="<?php echo htmlspecialchars($GameType); ?>"><?php echo $GameType; ?></option><?php
			} ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="right">Alliance Max Players</td>
		<td><input type="number" size="6" name="alliance_max_players" value="25"></td>
	</tr>
	<tr>
		<td class="right">Alliance Max Vets</td>
		<td><input type="number" size="6" name="alliance_max_vets" value="15"></td>
	</tr>
	<tr>
		<td class="right">Starting Credits</td>
		<td><input type="number" size="6" name="starting_credits" value="100000"></td>
	</tr>
	<tr>
		<td class="right">Ignore Stats</td>
		<td>
			Yes: <input type="radio" name="ignore_stats" id="InputFields" value="Yes" /><br />
			No: <input type="radio" name="ignore_stats" id="InputFields" value="No" checked="checked" /><br />
		</td>
	</tr>
	<tr>
		<td class="center" colspan="2"><input type="submit" value="Create Game" name="submit"></td>
	</tr>
	</table>
</form>
<br /><br />

<form method="POST" action="<?php echo $EditGameHREF; ?>">
	<table class="standard">
		<tr>
			<td class="right">
				<select name="game_id"><?php
					foreach($EditGames as $Game) {
						?><option value="<?php echo $Game['ID']; ?>"><?php echo $Game['GameName']; ?></option><?php
					} ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="center"><input type="submit" value="Edit" name="Edit"></td>
		</tr>
	</table>
</form>
