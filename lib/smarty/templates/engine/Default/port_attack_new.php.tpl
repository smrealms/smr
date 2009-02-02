{include_template template="includes/PortFullCombatResults.inc" assign=Template}{include file=$Template}<br />
<br />
<div align="center">
	{if !$OverrideDeath && !$Port->isDestroyed()}
		<div style="width:50%">
			<div class="buttonA">
				<a href="{$Port->getAttackHREF()}" class="buttonA">Continue Attack</a>
			</div>
		</div>
	{else}
		{if $OverrideDeath}
			<span style="color;red;">You have been destroyed.</span>
		{else}
			<span style="color;yellow;">You have destroyed the port.</span>
		{/if}<br />
		<div class="buttonA">
			{if $OverrideDeath}
				<a href="{$Globals->getPodScreenHREF()}" class="buttonA">Let there be pod</a>
			{else}
				<a href="{$Globals->getCurrentSectorHREF()}" class="buttonA">Current Sector</a>&nbsp;
				<a href="{$Port->getClaimHREF()}" class="buttonA">Claim this port for your race</a>&nbsp;
				<a href="{$Port->getLootHREF()}" class="buttonA">Loot the port</a>
			{/if}
		</div>
	{/if}
</div>