<form name="FORM" method="POST" action="<?php echo $CreateUniverseFormHref ?>">
	<p>
		<table border="0" cellpadding="3">
			<tr>
				<td>&nbsp;</td>
				<td>Create a Game</td>
				<td width="50">&nbsp;</td>
				<td>Choose existing Game</td>
			</tr>
			<tr>
				<td align="right">Name:</td>
				<td><input type="text" name="game_name" id="InputFields"></td>
				<td>&nbsp;</td>
				<td>
					<select name="game_id" size="1" id="InputFields">
						<?php foreach($Games as $Game)
						{
							?><option value="<?php echo $Game['ID'] ?>"><?php echo $Game['Name'] ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td align="right" valign="top">Description:</td>
				<td><textarea name="game_description" id="InputFields" style="height:100px;width:153px;"></textarea></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="right">Start Date (DD/MM/YYYY):</td>
				<td><input type="text" name="start_date" value="<?php echo $DefaultStartDate ?>" id="InputFields"></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="right">End Date (DD/MM/YYYY):</td>
				<td><input type="text" name="end_date" value="<?php echo $DefaultEndDate ?>" id="InputFields"></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="right">Max Players:</td>
				<td><input type="text" name="max_player" id="InputFields" value="5000"></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="right">Credits Needed:</td>
				<td><input type="text" name="credits" id="InputFields" value="0"></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="right">Speed:</td>
				<td><input type="text" name="speed" id="InputFields" value="1"></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="right">Game Type:</td>
				<td>
					<select name="game_type" size="1" id="InputFields">
						<option selected>Default</option>
						<option>1.2</option>
						<option>Semi Wars</option>
						<option>Race_Wars</option>
					</select>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr><td colspan="4">&nbsp;</td></tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<input type="submit" name="action" value="Create >>" id="InputFields">
				</td>
				<td>&nbsp;</td>
				<td>
					<input type="submit" name="action" value="Next >>" id="InputFields">
				</td>
			</tr>
		</table>
	</p>
	<p>&nbsp;</p>
</form><?php

if($DisabledGames)
{ ?>
	<p>The following games haven\'t been approved yet, but are in the database!<br />
		So they are <b>NOT</b> visible on the Game-Play Page!
	</p>
		<ul><?php
			foreach($DisabledGames as $GameName)
			{
				?><li><?php echo $GameName ?></li><?php
			} ?>
		</ul><?php
}
else
{
	?>All games are approved!<?php
} ?>