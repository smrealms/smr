{if $CombatResultsType=='TRADER'}
	Sector {$CombatLogSector}<br/>
	{$CombatLogTimestamp}<br/>
	<br/>
							
	{include_template template="includes/TraderFullCombatResults.inc" assign=Template TraderCombatResults=$CombatResults}{include file=$Template}
{elseif $CombatResultsType=='FORCE'}
	Sector {$CombatLogSector}<br/>
	{$CombatLogTimestamp}<br/>
	<br/>
							
	{include_template template="includes/ForceFullCombatResults.inc" assign=Template FullForceCombatResults=$CombatResults}{include file=$Template}
{elseif $CombatResultsType=='PORT'}
	{$PHP_OUTPUT}
{elseif $CombatResultsType=='PLANET'}
	{$PHP_OUTPUT}
{else}
	{$PHP_OUTPUT}
{/if}