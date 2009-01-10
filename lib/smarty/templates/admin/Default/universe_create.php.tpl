<form name="FORM" method="POST" action="{$CreateUniverseFormAction}">
	<input type="hidden" name="sn" value="{$CreateUniverseFormSN}">
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
						{foreach from=$Games item=Game}
							<option value="{$Game.ID}">{$Game.Name}</option>
						{/foreach}
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
				<td align="right">Start Date:</td>
				<td><input type="text" name="start_date" value="{$DefaultStartDate}" id="InputFields"></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="right">End Date:</td>
				<td><input type="text" name="end_date" value="{$DefaultEndDate}" id="InputFields"></td>
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
						<option selected>Old_School</option>
						<option>Semi_Wars</option>
						<option>Race_Wars</option>
						<option>Tournament</option>
						<option>Random Alliance</option>
						<option>Blue_Sky</option>
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
</form>

{if $DisabledGames}
	<p>The following games haven\'t been approved yet, but are in the database!<br />
		So they are <b>NOT</b> visible on the Game-Play Page!
	</p>
		<ul>
			{foreach from=$DisabledGames item=GameName}
				<li>{$GameName}</li>
			{/foreach}
		</ul>
{else}
	All games are approved!
{/if}