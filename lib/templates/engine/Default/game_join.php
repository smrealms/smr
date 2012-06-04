<table class="standard">
	<tr>
		<th width="150">Game Name</th>
		<th>Start Date</th>
		<th>End Date</th>
		<th>Max Players</th>
		<th>Type</th>
		<th>Game Speed</th>
		<th>Credits Needed</th>
	</tr>
	<tr>
		<td width="40%"><?php echo $Game['Name'] ?> (<?php echo $Game['ID'] ?>)</td>
		<td><?php echo date(DATE_DATE_SHORT,$Game['StartDate']); ?></td>
		<td><?php echo date(DATE_DATE_SHORT,$Game['EndDate']); ?></td>
		<td><?php echo $Game['MaxPlayers'] ?></td>
		<td><?php echo $Game['Type'] ?></td>
		<td><?php echo $Game['Speed'] ?></td>
		<td><?php echo $Game['Credits'] ?></td>
	</tr>
</table><br />
<p><?php echo $Game['Description'] ?></p>
<form name="FORM" method="POST" action="<?php echo $JoinGameFormLink ?>">
	<input type="hidden" name="sn" value="<?php echo $JoinGameFormSN ?>">
	<h1>Create Merchant</h1><br />
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td>
				<p>Each Space Merchant Realms game requires you to create a new Merchant. For this purpose you must choose a name and a race.<br />
				To enhance the roleplaying atmosphere of the game, there are certain criteria your name must meet.<br />
				The following names will not be accepted:
				<ul>
				<li>Names with references to "out of character" information - ie. something that would make sense only to the player, not the character - such as "SpaceGamer", "SMR Rules" etc.</li>
				<li>Names that are taken from real life history, or the names of existing people - eg. "Osama bin Laden", "Stalin" or "Harrison Ford".</li>
				<li>Names that convey an attitude towards yourself or someone else - such as "Lamer" or "Shadow Sucks".</li>
				<li>Names that make excessive use of special characters, eg. "~-=[Daron]=-~" should be "Daron" instead.</li>
				<li>Names that look similar or identical to another player in an attempt to trick other players are prohibited.</li>
				</ul>
				If you disregard these rules, your player will be deleted, so choose your name wisely.</p><br />
				<br />
				<table border="0" cellpadding="3">
					<tr>
						<td align="right"><b>Name:</b></td>
						<td><input type="text" name="player_name" maxlength="32" class="InputFields"></td>
						<td rowspan="4" class="standard"><img name="race_image" src="images/race1.gif" alt="Please select a race."></td>
					</tr>
					<tr>
						<td align="right"><b>Race:</b></td>
						<td>
						<select name="race_id" size="1" style="border-width:0px;width:150px;" OnChange="go();">
							<option value="1">[please select]</option><?php
							foreach($Races as $Race)
							{
								?><option value="<?php echo $Race['ID'] ?>"><?php echo $Race['Name'] ?> (<?php echo $Race['NumberOfPlayers'] ?> Traders)<?php
							} ?>
						</select>
						</td>
					</tr>
					
					<tr>
						<td align="right">&nbsp;</td>
						<td><input type="submit" name="action" value="Create Player" class="InputFields">
						</td>
					</tr>
					
					<tr>
						<td colspan="2">
							<textarea name="race_descr" class="InputFields" style="width:300px;height:275px;border:0;"></textarea>
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
							<img width="440" height="440" border="0" name="graph" id="graphframe" src="images/graph1.gif" alt="Race overview" />
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
</form>

<script type="text/javascript">
var	desc = new Array(<?php echo $RaceDescriptions; ?>);
function go()
{
	var race_id = document.forms[0].race_id.options[document.forms[0].race_id.selectedIndex].value;
	document.race_image.src = "images/race" + race_id + ".gif";
	document.getElementById('graphframe').src = "images/graph" + race_id + ".gif";
	document.FORM.race_descr.value = desc[race_id - 1];
}
</script>