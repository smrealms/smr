{if !$AlreadyDestroyed}
	<h1>Attacker Results</h1><br />
	{include_template template="includes/PlanetTraderTeamCombatResults.inc" assign=Template}{include file=$Template TraderTeamCombatResults=$FullPlanetCombatResults.Attackers}
	<br />
	<br />
{else}
	<span style="font-weight:bold;">The planet is already destroyed.</span><br /><br />
{/if}
<img src="images/planetAttack.jpg" width="480px" height="330px" alt="Planet Attack" title="Planet Attack"><br />
{if !$AlreadyDestroyed}
	<br />
	<h1>Planet Results</h1><br />
	{include_template template="includes/PlanetCombatResults.inc" assign=Template}{include file=$Template PlanetCombatResults=$FullPlanetCombatResults.Planet}
{/if}