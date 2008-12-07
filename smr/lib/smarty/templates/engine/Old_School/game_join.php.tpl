
<script language="Javascript">
	function go()
	{ldelim}
		desc = new Array({$RaceDescriptions});
		var race_id = document.forms[0].race_id.options[document.forms[0].race_id.selectedIndex].value;
		document.race_image.src = "images/race" + race_id + ".gif";
		document.getElementById('graphframe').src = "images/graph" + race_id + ".gif";
		document.FORM.race_descr.value = desc[race_id - 1];
	{rdelim}
</script>

<p>
	<table class="standard" cellspacing="0">
		<tr>
			<th width="150">Game Name</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Max Players</th>
			<th>Type</th>
			<th>Game Speed</th>
			<th>Credits Needed</th>
		</tr>
		</tr>
		<tr>
			<td width="40%">{$Game.Name} ({$Game.ID})</td>
			<td>{$Game.StartDate}</td>
			<td>{$Game.EndDate}</td>
			<td>{$Game.MaxPlayers}</td>
			<td>{$Game.Type}</td>
			<td>{$Game.Speed}</td>
			<td>{$Game.Credits}</td>
		</tr>
	</table><br />
</p>
<p>{$Game.Description}</p>
<form name="FORM" method="POST" action="{$JoinGameFormLink}">
	<input type="hidden" name="sn" value="{$JoinGameFormSN}">
	<p>
		<h1>Create Merchant</h1><br />
		<table cellspacing="0" cellpadding="0" class="nobord nohpad">
			<tr>
				<td>
					<span>Each Space Merchant Realms game requires you to create a new Merchant. For this purpose you must choose a name and a race.<br />
					To enhance the roleplaying atmosphere of the game, there are certain criteria your name must meet.<br />
					The following names will not be accepted:
					<ul>
					<li>Names with references to "out of character" information - ie. something that would make sense only to the player, not the character - such as "SpaceGamer", "SMR Rules" etc.</li>
					<li>Names that are taken from real life history, or the names of existing people - eg. "Osama bin Laden", "Stalin" or "Harrison Ford".</li>
					<li>Names that convey an attitude towards yourself or someone else - such as "Lamer" or "Shadow Sucks".</li>
					<li>Names that make excessive use of special characters, eg. "~-=[Daron]=-~" should be "Daron" instead.</li>
					<li>Names that look similar or identical to another player in an attempt to trick other players are prohibited.</li>
					</ul>
					If you disregard these rules, your player will be deleted, so choose your name wisely.</span><br />
					<br />
					<table border="0" cellpadding="3">
						<tr>
							<td align="right"><b>Name:</b></td>
							<td><input type="text" name="player_name" maxlength="32" id="InputFields"></td>
							<td rowspan="4" class="standard"><img name="race_image" src="images/race1.gif"></td>
						</tr>
						<tr>
							<td align="right"><b>Race:</b></td>
							<td>
							<select name="race_id" size="1" style="border-width:0px;width:150px;" OnChange="go();">
								<option value="1">[please select]</option>
								{foreach from=$Races item=Race}
									<option value="{$Race.ID}">{$Race.Name} ({$Race.NumberOfPlayers} Traders)
								{/foreach}
							</select>
							</td>
						</tr>
						
						<tr>
							<td align="right">&nbsp;</td>
							<td><input type="submit" name="action" value="Create Player" id="InputFields">
							</td>
						</tr>
						
						<tr>
							<td colspan="2">
								<textarea name="race_descr" id="InputFields" style="width:300px;height:275px;border:0;"></textarea>
							</td>
						</tr>
						
					</table>	
				</td>
			</tr>
						
			<tr>
				<td align=center>
					<table>
						<tr>
							<td align=center colspan=4 class="center">Trading</td>
						</tr>
						<tr>
							<td align=left>Combat<br />
							Strength</td>
							<td align=center colspan=2>
								<img width="440" height="440" border="0" name="graph" id="graphframe" src="images/graph1.gif"/>
							</td>
							<td align=right>Hunting</td>
						</tr>
						<tr>
							<td align=center colspan=4 class="center">Utility</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</p>	
</form>