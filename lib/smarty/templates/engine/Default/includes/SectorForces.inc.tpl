<span id="players_cs">
	{if $ThisSector->hasForces()}
		{assign var=Forces value=$ThisSector->getForces()}
		{assign var=RefreshAny value=false}
		<table cellspacing="0" class="standard fullwidth">
			<tbody>
				<tr>
					<th style="background: rgb(0, 0, 85) none repeat scroll 0% 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; color: rgb(128, 200, 112);" colspan="6">Forces (1)</th>
				</tr>
				<tr>
					<th>Mines</th>
					<th>Combat</th>
					<th>Scout</th>
					<th>Expires</th>
					<th>Owner</th>
					<th>Option</th>
				</tr>
				{foreach from=$Forces item=Force}
				{assign var=Owner value=$Force->getOwner()}
				{assign var=SharedForceAlliance value=$Owner->sharedForceAlliance($ThisPlayer)}
				{if $SharedForceAlliance}{assign var=RefreshAny value=true}{/if}
				
					<tr>
						<td class="center shrink nowrap">
							{if $SharedForceAlliance && $ThisShip->canAcceptMines()&&$Force->hasMines()}<a href="{$Force->getTakeMineHREF()}">[-]</a>{/if}{$Force->getMines()}{if $SharedForceAlliance && $ThisShip->hasMines()&&$Force->canAcceptMines()}<a href="{$Force->getDropMineHREF()}">[+]</a>{/if}
						</td>
						<td class="center shrink nowrap">
							{if $SharedForceAlliance && $ThisShip->canAcceptCDs()&&$Force->hasCDs()}<a href="{$Force->getTakeCDHREF()}">[-]</a>{/if}{$Force->getCDs()}{if $SharedForceAlliance && $ThisShip->hasCDs()&&$Force->canAcceptCDs()}<a href="{$Force->getDropCDHREF()}">[+]</a>{/if}
						</td>
						<td class="center shrink nowrap">
							{if $SharedForceAlliance && $ThisShip->canAcceptSDs()&&$Force->hasSDs()}<a href="{$Force->getTakeSDHREF()}">[-]</a>{/if}{$Force->getSDs()}{if $SharedForceAlliance && $ThisShip->hasSDs()&&$Force->canAcceptSDs()}<a href="{$Force->getDropSDHREF()}">[+]</a>{/if}
						</td>
						<td class="shrink nowrap center">
							{if $SharedForceAlliance}
								<span class="green">{$Force->getExpire()|date:DATE_FULL_SHORT}</span>
							{else}
								<span class="red">WAR</span>
							{/if}
						</td>
						<td>
							<a href="{$Owner->getTraderSearchHREF()}">{$Owner->getDisplayName()}</a> ({if $Owner->hasAlliance()}<a href="{$Owner->getAllianceRosterHREF()}">{/if}{$Owner->getAllianceName()}{if $Owner->hasAlliance()}</a>{/if})
						</td>
						<td align="center" class="shrink center">
							
							<div class="buttonA">
								<a href="{if $SharedForceAlliance}{$Force->getExamineDropForcesHREF()}{else}{$Force->getExamineAttackForcesHREF()}{/if}" class="buttonA"> Examine </a>
							</div>
							{if $SharedForceAlliance}
								<br />
								<br />
								<div class="buttonA">
									<a href="{$Force->getRefreshHREF()}" class="buttonA"> Refresh </a>
								</div>
							{/if}
						</td>
					</tr>
				{/foreach}
				{if $RefreshAny}
					<tr>
						<td class="center" colspan="6">
							<div class="buttonA"><a href="{$Force->getRefreshAllHREF()}" class="buttonA"> Refresh All </a></div>
						</td>
					</tr>
				{/if}
			</tbody>
		</table>
	{/if}
</span>