<a href="{$EditDummysLink}">Edit Combat Dummys</a><br /><br />

{if $Duplicates}<h1>Do not use duplicate dummy names, they would interfere with each other</h1>{/if}

<form action="{$CombatSimHREF}" method="POST">
	<table>
		<tr>
			<td style="vertical-align:top">
	Attackers<br />
	{include_template template="includes/CombatSimTeamDetails.inc" assign=Template}{include file=$Template Team=$Attackers MemberDescription="Attacker" MemberInputName="attackers"}
			</td>
		</tr>
		<tr>
			<td style="vertical-align:top">
	Defenders<br />
	{include_template template="includes/CombatSimTeamDetails.inc" assign=Template}{include file=$Template Team=$Defenders MemberDescription="Defender" MemberInputName="defenders"}
			</td>
		</tr>
		<tr>
			<td colspan = "2" style="text-align:center">
				<br />All drones, shields, armour assumed full at the start of the simulation<br /><br />
				<input type="submit" name="update" value="Update Details" />&nbsp;
				<input type="submit" name="run" value="Run Simulation" />
				<input type="submit" name="death_run" value="Run Simulation TO THE DEATH!!" />
			</td>
		</tr>
	</table>
</form>
{if $TraderCombatResults}{include_template template="includes/TraderFullCombatResults.inc" assign=Template}{include file=$Template}<br />{/if}