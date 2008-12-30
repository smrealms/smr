<table>
	<tr>
		<th>Name</th>
		<th>Processor</th>
		<th>Image</th>
		<th>Fed</th>
		<th>Bar</th>
		<th>Bank</th>
		<th>HQ</th>
		<th>UG</th>
		<th></th>
	</tr>
	{foreach from=$Locations item=Location}
	<tr>
		<td>{$Location->getName()}</td>
		<td>{$Location->getProcessor()}</td>
		<td>{$Location->isFed()}</td>
		<td>{$Location->isBar()}</td>
		<td>{$Location->isBank()}</td>
		<td>{$Location->isHQ()}</td>
		<td>{$Location->isUG()}</td>
		<td>
			{foreach from=$Locations->getHardwareSold() item=Hardware}
				{$Hardware}<br />
			{/foreach}
		</td>
		<td>
			{foreach from=$Locations->getShipsSold() item=Ship}
				{$Ship.Name}<br />
			{/foreach}
		</td>
		<td>
			{foreach from=$Locations->getWeaponsSold item=Weapon}
				{$Weapon->getName()}<br />
			{/foreach}
		</td>
	</tr>
	{/foreach}
</table>