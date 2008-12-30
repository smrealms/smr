<a href="{$EditDummysLink}">Edit Dummys</a>

{if $Duplicates}<h1>Do not use duplicate dummy names, they would interfere with each other</h1>{/if}

<form action="{$CombatSimHREF}" method="POST">
	<table>
		<tr>
			<td style="vertical-align:top">
	Attackers<br />
	{include_template template="includes/CombatSimTeamDetails.inc" assign=Template}{include file=$Template Team=$Attackers MemberDescription="Attacker"}
			</td>
		</tr>
		<tr>
			<td style="vertical-align:top">
	Defenders<br />
	{include_template template="includes/CombatSimTeamDetails.inc" assign=Template}{include file=$Template Team=$Defenders MemberDescription="Defender"}
			</td>
		</tr>
		<tr>
			<td colspan = "2" style="text-align:center">
				<br />All drones, shields, armour assumed full at the start of the simulation<br /><br />
				<input type="submit" value="Update Details" />&nbsp;
				<input type="submit" value="Run Simulation" />
			</td>
		</tr>
	</table>
</form>