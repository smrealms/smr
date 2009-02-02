{if $FullForceCombatResults.Forced}
	<h1>Force Results</h1><br />
	{include_template template="includes/ForcesCombatResults.inc" assign=Template}{include file=$Template ForcesCombatResults=$FullForceCombatResults.Forces}
{else}
	<h1>Attacker Results</h1><br />
	{include_template template="includes/ForceTraderTeamCombatResults.inc" assign=Template}{include file=$Template TraderTeamCombatResults=$FullForceCombatResults.Attackers}
{/if}
<br />
<br />
<img src="images/creonti_cruiser.jpg" alt="Creonti Cruiser" title="Creonti Cruiser"><br />
<br />
{if !$FullForceCombatResults.Forced}
	<h1>Force Results</h1><br />
	{include_template template="includes/ForcesCombatResults.inc" assign=Template}{include file=$Template ForcesCombatResults=$FullForceCombatResults.Forces}
{else}
	<h1>Defender Results</h1><br />
	{include_template template="includes/ForceTraderTeamCombatResults.inc" assign=Template}{include file=$Template TraderTeamCombatResults=$FullForceCombatResults.Attackers}
{/if}