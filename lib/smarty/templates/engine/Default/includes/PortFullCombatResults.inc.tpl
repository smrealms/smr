{if !$AlreadyDestroyed}
	<h1>Attacker Results</h1><br />
	{include_template template="includes/PortTraderTeamCombatResults.inc" assign=Template}{include file=$Template TraderTeamCombatResults=$FullPortCombatResults.Attackers}
	<br />
	<br />
{else}
	<span style="font-weight:bold;">The port is already destroyed.</span><br /><br />
{/if}
<img src="images/portAttack.jpg" width="480px" height="330px" alt="Port Attack" title="Port Attack"><br />
{if !$AlreadyDestroyed}
	<br />
	<h1>Port Results</h1><br />
	{include_template template="includes/PortCombatResults.inc" assign=Template}{include file=$Template PortCombatResults=$FullPortCombatResults.Port}
{/if}