{include_template template="includes/ForceFullCombatResults.inc" assign=Template}{include file=$Template}<br />
<br />
<div align="center">
	{if isset($Target)}
		<div style="width:50%">
			<div class="buttonA">
				<a href="{$Target->getAttackForcesHREF()}" class="buttonA">Continue Attack</a>
			</div>
		</div>
	{else}
		{if $OverrideDeath}
			<span style="color;red;">You have been destroyed.</span>
		{else}
			<span style="color;yellow;">You have destroyed the forces.</span>
		{/if}<br />
		<div class="buttonA">
			{if $OverrideDeath}
				<a href="{$Globals->getPodScreenHREF()}" class="buttonA">Let there be pod</a>
			{else}
				<a href="{$Globals->getCurrentSectorHREF()}" class="buttonA">Current Sector</a>
			{/if}
		</div>
	{/if}
</div>