{foreach from=$Locations item=Location}
<tr>
	<td>{$Location->getName()}</td>
	<td>{$Location->getAction()}</td>
	<td>{$Location->getImage()}</td>
	<td>{$Location->isFed()}</td>
	<td>{$Location->isBar()}</td>
	<td>{$Location->isBank()}</td>
	<td>{$Location->isHQ()}</td>
	<td>{$Location->isUG()}</td>
	<td>
		{foreach from=$Location->getHardwareSold() item=Hardware}
			{$Hardware}<br />
		{/foreach}
	</td>
	<td>
		{foreach from=$Location->getShipsSold() item=Ship}
			{$Ship.Name}<br />
		{/foreach}
	</td>
	<td>
		{foreach from=$Location->getWeaponsSold() item=Weapon}
			{$Weapon->getName()}<br />
		{/foreach}
	</td>
	<td>
		<div class="buttonA">
			<a href="{$Location->getEditHREF()}" class="buttonA"> Edit </a>
		</div>
	</td>
</tr>
{/foreach}