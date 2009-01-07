<span id="players_cs">
	{if $ThisSector->hasOtherTraders()}
		{assign var=Players value=$ThisSector->getOtherTraders()}
		{if $ThisPlayer->canSeeAny($Players)}
			<table cellspacing="0" class="standard fullwidth">
				<tr>
					<th style="background: rgb(85, 0, 0) none repeat scroll 0% 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;" colspan="5">Ships ({$Players|@count})</th>
				</tr>
				<tr>
					<th>Trader</th>
					<th>Ship</th>
					<th>Rating</th>
					<th>Level</th>
					<th>Option</th>
				</tr>
				{foreach from=$Players item=Player}
					{if $ThisPlayer->canSee($Player)}
						{assign var=Ship value=$Player->getShip()}
						<tr>
							<td>
								<a href="{$Player->getTraderSearchHREF()}">{$Player->getDisplayName()}</a> ({if $Player->hasAlliance()}<a href="{$Player->getAllianceRosterHREF()}">{/if}{$Player->getAllianceName()}{if $Player->hasAlliance()}</a>{/if})
							</td>
							<td>{if $Ship->hasActiveIllusion()}{$Ship->getIllusionShipName()}{else}{$Ship->getName()}{/if}</td>
							<td class="shrink center nowrap">{if $Ship->hasActiveIllusion()}{$Ship->getIllusionAttack()}{else}{$Ship->getAttackRating()}{/if} / {if $Ship->hasActiveIllusion()}{$Ship->getIllusionDefense()}{else}{$Ship->getDefenseRating()}{/if}</td>
							<td class="shrink center nowrap">{$Player->getLevelID()}</td>
							<td class="shrink center nowrap">
								<div class="buttonA">
									<a href="{$Player->getExamineTraderHREF()}" class="buttonA"> Examine </a>
								</div>
							</td>
						</tr>
					{/if}
				{/foreach}
			</table>
		{else}
			<span class="red bold">WARNING:</span> Sensors have detected the presence of cloaked vessels in this sector<br /><br />
		{/if}
	{/if}
</span>