<h1>Attacker Results</h1><br />
{include_template template="includes/TeamCombatResults.inc" assign=Template}{include file=$Template TeamCombatResults=$TraderCombatResults.Attackers}<br />
<br />
<img src="images/creonti_cruiser.jpg" alt="Creonti Cruiser" title="Creonti Cruiser"><br />
<br />
<h1>Defender Results</h1><br />
{include_template template="includes/TeamCombatResults.inc" assign=Template}{include file=$Template TeamCombatResults=$TraderCombatResults.Defenders}<br />
<br />
<div align="center">
	{if isset($Target)}{assign_random var=RandomPosition min=0 max=2}
		<div style="width:50%" align="{if $RandomPosition == 0}center{elseif $RandomPosition == 1}right{else}left{/if}">
			<div class="buttonA">
				<a href="{$Target->getAttackTraderHREF()}" class="buttonA">Continue Attack</a>
			</div>
		</div>
	{else}
		<h2>The battle has ended!</h2><br />
		<div class="buttonA">
			{if $OverrideDeath}
				<a href="{$Globals->getPodScreenHREF()}" class="buttonA">Let there be pod</a>
			{else}
				<a href="{$Globals->getCurrentSectorHREF()}" class="buttonA">Current Sector</a>
			{/if}
		</div>
	{/if}
</div>