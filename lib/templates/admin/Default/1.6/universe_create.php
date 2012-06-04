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
		<td class="left"><input type="text" size="32" name="game_name" value=""></td>
	</tr>
	<tr>
		<td class="right">Game Description</td>
		<td class="left"><textarea name="desc" value=""></textarea></td>
	</tr>
	<tr>
		<td class="right">Game Speed</td>
		<td class="left"><input type="text" size="6" name="game_speed" value="1.25"></td>
	</tr>
	<tr>
		<td class="right">Max Players</td>
		<td class="left"><input type="text" size="6" name="max_players" value="5000"></td>
	</tr>
	<tr>
		<td class="right">Number of Galaxies</td>
		<td class="left"><input type="text" size="5" name="num_gals" value="12"></td>
	</tr>
	<tr>
		<td class="right">Start Date (YYYY/MM/DD)</td>
		<td class="left"><input type="text" size="32" name="game_start" value="<?php echo date(DATE_DATE_SHORT,TIME) ?>"></td>
	</tr>
	<tr>
		<td class="right">End Date (YYYY/MM/DD)</td>
		<td class="left"><input type="text" size="32" name="game_end" value="<?php echo date(DATE_DATE_SHORT,$DefaultEnd) ?>"></td></tr>
	<tr>
		<td class="right">Credits Required</td>
		<td class="left"><input type="text" size="5" name="creds_needed" value="0"></td>
	</tr>
	<tr>
		<td class="right">Game Type</td>
		<td class="left">
			<select name="game_type" id="InputFields"><?php
			foreach($GameTypes as $GameType)
			{
				?><option value="<?php echo $GameType; ?>"><?php echo $GameType; ?></option><?php
			} ?>
			</select>
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
				<select name="game_id">
				<?php
				foreach($EditGames as $Game)
				{
					?><option value="<?php echo $Game['GameID']; ?>"><?php echo $Game['GameName']; ?></option><?php
				} ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="center"><input type="submit" value="Edit" name="Edit"></td>
		</tr>
	</table>
</form>
