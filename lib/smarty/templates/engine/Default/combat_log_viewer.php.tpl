{if $CombatResultsType=='PLAYER'}
	Sector {$CombatLogSector}<br/>
	{$CombatLogTimestamp}<br/>
	<br/>

	{include_template template="includes/TraderFullCombatResults.inc" assign=Template}{include file=$Template TraderCombatResults=$CombatResults}
{elseif $CombatResultsType=='FORCE'}
	Sector {$CombatLogSector}<br/>
	{$CombatLogTimestamp}<br/>
	<br/>
							
	{include_template template="includes/ForceFullCombatResults.inc" assign=Template}{include file=$Template FullForceCombatResults=$CombatResults}
{elseif $CombatResultsType=='PORT'}
	{$CombatResults}
{elseif $CombatResultsType=='PLANET'}
	{$CombatResults}
{else}
	{$PHP_OUTPUT}
{/if}