<form action="{$EditDummysLink}" method="GET">
	Edit Dummy:
	<select name="dummyname">
		{foreach from=$DummyNames item=DummyName}
			<option value="{$DummyName}" selected="selected">{$DummyName}</option>
	</select><br />
	<input type="submit" value="Select Dummy" />
</form>

<table>
	<tr>
		<td style="vertical-align:top">
Attackers<br />
<table>
	<tr>
		<td style="vertical-align:top">
			<u>Player One</u><br/><br />
			<form action="/loader.php?sn=6233138d" method="POST">
				<input type="hidden" name="action" value="1" /><input type="hidden" name="stored" value="YToyOntpOjE7YTo0OntpOjA7aTowO2k6MTtpOjE7aToyO2k6MDtpOjM7YToxOntpOjA7aToxO319aToyO2E6NDp7aTowO2k6MDtpOjE7aToxO2k6MjtpOjA7aTozO2E6MTp7aTowO2k6MTt9fX0=" />
				Level:&nbsp;<select name="level"><option value="0" selected="selected">0</option></select>
				&nbsp;Ship:&nbsp;<select name="ship_id"><option value="1" selected="selected">Galactic Semi</option></select>
				&nbsp;DCS&nbsp;<input type="checkbox" name="DCS" />
				<input type="submit" value="Alter Player One" /><br /><br />
				Weapon: 1&nbsp;<select name="weapon_0"><option value="1" selected="selected">Newbie Pulse Laser (dmg: 40/40 acc: 65% lvl:3)</option></select><br />
			</form>
		</td>
		<td style="vertical-align:top">
			<u>Current Details</u><br/><br/>Level: 0<br />Ship: Galactic Semi<br />DCS: false<br/>Weapons:<br/>Newbie Pulse Laser<br />
		</td>
	</tr>
</table>
		</td>
	</tr>
	<tr>
		<td style="vertical-align:top">
Defenders<br />
<table>
	<tr>
		<td style="vertical-align:top">
			<u>Player Two</u><br/><br />
			<form action="/loader.php?sn=6233138d" method="POST">
				<input type="hidden" name="action" value="2" /><input type="hidden" name="stored" value="YToyOntpOjE7YTo0OntpOjA7aTowO2k6MTtpOjE7aToyO2k6MDtpOjM7YToxOntpOjA7aToxO319aToyO2E6NDp7aTowO2k6MDtpOjE7aToxO2k6MjtpOjA7aTozO2E6MTp7aTowO2k6MTt9fX0=" />
				Level:&nbsp;<select name="level"><option value="0" selected="selected">0</option></select>
				&nbsp;Ship:&nbsp;<select name="ship_id"><option value="1" selected="selected">Galactic Semi</option></select>
				&nbsp;DCS&nbsp;<input type="checkbox" name="DCS" /><input type="submit" value="Alter Player Two" /><br /><br />Weapon: 1&nbsp;<select name="weapon_0"><option value="1" selected="selected">Newbie Pulse Laser (dmg: 40/40 acc: 65% lvl:3)</option></select><br />
			</form>
		</td>
		<td style="vertical-align:top">
			<u>Current Details</u><br/><br/>Level: 0<br />Ship: Galactic Semi<br />DCS: false<br/>Weapons:<br/>Newbie Pulse Laser<br /></td></tr><tr><td colspan = "2" style="text-align:center"><br />All drones, shields, armour assumed full at the start of the simulation<br/><br /></form><form action="/loader.php?sn=6233138d" method="POST"><input type="hidden" name="action" value="3" /><input type="hidden" name="stored" value="YToyOntpOjE7YTo0OntpOjA7aTowO2k6MTtpOjE7aToyO2k6MDtpOjM7YToxOntpOjA7aToxO319aToyO2E6NDp7aTowO2k6MDtpOjE7aToxO2k6MjtpOjA7aTozO2E6MTp7aTowO2k6MTt9fX0=" /><input type="submit" value="Run Simulation" /></form>
		</td>
	</tr>
</table>
		</td>
	</tr>
</table>