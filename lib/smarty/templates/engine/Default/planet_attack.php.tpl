{include_template template="includes/PlanetFullCombatResults.inc" assign=Template}{include file=$Template}<br />
<br />
<div align="center">
	{if !$OverrideDeath && !$Planet->isDestroyed()}
		<div style="width:50%">
			<div class="buttonA">
				<a href="{$Planet->getAttackHREF()}" class="buttonA">Continue Attack</a>
			</div>
		</div>
	{else}
		{if $OverrideDeath}
			<span style="color;red;">You have been destroyed.</span>
		{else}
			<span style="color;yellow;">You have destroyed the planet.</span>
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