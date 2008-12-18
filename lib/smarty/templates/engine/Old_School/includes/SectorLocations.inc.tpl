{if $ThisSector->hasLocation()}
	{assign var=Locations value=$ThisSector->getLocations()}
	<table cellspacing="0" cellpadding="0" class="standard csl">
		<tr>
			<th>Location</th>
			{if $ThisSector->hasAnyLocationsWithAction()}<th>Option</th>{/if}
		</tr>
		{foreach from=$Locations item=Location}
			<tr>
				<td{if !$Location->hasAction() && $ThisSector->hasAnyLocationsWithAction()} colspan="2"{/if}>
					<img align="left"src="{$Location->getImage()}" alt="{$Location->getName()}" title="{$Location->getName()}" /> {$Location->getName()}
				</td>
				{if $Location->hasAction()} 
					<td class="shrink nowrap">
						<div class="buttonA"><a class="buttonA" href="{$Location->getExamineHREF()}">&nbsp;Examine&nbsp;</a></div>
					</td>
				{/if}
			</tr>
		{/foreach}
	</table><br />
{/if}