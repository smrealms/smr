{if $CombatResultsType}
	{if $PreviousLogHREF || $NextLogHREF}
		<div class="center">
			{if $PreviousLogHREF}
				<a href="{$PreviousLogHREF}"><img title="Previous" alt="Previous" src="images/album/rew.jpg" /></a>
			{elseif $NextLogHREF}
				<a href="{$NextLogHREF}"><img title="Next" alt="Next" src="images/album/fwd.jpg" /></a>
			{/if}
		</div>
	{/if}
	Sector {$CombatLogSector}<br/>
	{$CombatLogTimestamp}<br/>
	<br/>

	{if $CombatResultsType=='PLAYER'}
		{include_template template="includes/TraderFullCombatResults.inc" assign=Template}{include file=$Template TraderCombatResults=$CombatResults}
	{elseif $CombatResultsType=='FORCE'}
		{include_template template="includes/ForceFullCombatResults.inc" assign=Template}{include file=$Template FullForceCombatResults=$CombatResults}
	{elseif $CombatResultsType=='PORT'}
		{include_template template="includes/PortFullCombatResults.inc" assign=Template}{include file=$Template FullPortCombatResults=$CombatResults}
	{elseif $CombatResultsType=='PLANET'}
		{$CombatResults}
	{/if}
{else}
	{$PHP_OUTPUT}
{/if}