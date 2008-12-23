{if $TraderCombatResults}
	Sector {$CombatLogSector}<br/>
	{CombatLogTimestamp}<br/>
	<br/>
							
	{include_template template="includes/TraderFullCombatResults.inc" assign=Template}{include file=$Template}
{else}
	{$PHP_OUTPUT}
{/if}