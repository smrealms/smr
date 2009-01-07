{if $ThisSector->hasPlanet()}
	{assign var=Planet value=$ThisSector->getPlanet()}
	<table cellspacing="0" cellpadding="0" class="standard csl">
		<tr>
			<th>Planet</th>
			<th>Option</th>
		</tr>
		<tr>
			<td>
				<img align="left" src="images/planet.gif" alt="Planet" title="Planet" />
				&nbsp;{$Planet->getName()}&nbsp
				{if $Planet->isInhabitable()}
					<span class="green">Inhabitable</span>
				{else}
					<span class="red">Uninhabitable</span>
				{/if}
			</td>
			<td class="center nowrap shrink">
				<div class="buttonA">
					<a class="buttonA" href="{$Planet->getExamineHREF()}">&nbsp;Examine&nbsp;</a>
				</div>
			</td>
		</tr>
	</table><br />
{/if}