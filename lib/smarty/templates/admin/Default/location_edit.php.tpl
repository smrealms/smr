{if !$Locations}<a href="{$ViewAllLocationsLink}">View All Locations</a><br /><br />{/if}

<table>
	<tr>
		<th>Name</th>
		<th>Action</th>
		<th>Image</th>
		<th>Fed</th>
		<th>Bar</th>
		<th>Bank</th>
		<th>HQ</th>
		<th>UG</th>
		<th>Hardware</th>
		<th>Ships</th>
		<th>Weapons</th>
		<th>Edit</th>
	</tr>
{if $Locations}
	{include_template template="includes/ViewLocations.inc" assign=Template}{include file=$Template Locations=$Locations}
{else}
	<tr>
		<form action="{$Location->getEditHREF()}" method="POST">
			<td><input name="name" type="text" value="{$Location->getName()}" /></td>
			<td><input name="action" type="text" value="{$Location->getAction()}" /></td>
			<td><input name="image" type="text" value="{$Location->getImage()}" /></td>
			<td><input name="fed" type="checkbox" {if $Location->isFed()}checked="checked"{/if} /></td>
			<td><input name="bar" type="checkbox" {if $Location->isBar()}checked="checked"{/if} /></td>
			<td><input name="bank" type="checkbox" {if $Location->isBank()}checked="checked"{/if} /></td>
			<td><input name="hq" type="checkbox" {if $Location->isHQ()}checked="checked"{/if} /></td>
			<td><input name="ug" type="checkbox" {if $Location->isUG()}checked="checked"{/if} /></td>
			<td>
				<table>
					{foreach from=$Location->getHardwareSold() key=HardwareID item=Hardware}
						<tr>
							<td>{$Hardware}</td>
							<td><input type="checkbox" name="remove_hardware[]" value="{$HardwareID}" /></td>
						</tr>
					{/foreach}
					<tr>
						<td>Add Hardware:</td>
						<td>
							<select name="add_hardware_id">
								<option value="0">None</option>
								{foreach from=$AllHardware item=Hardware}
									<option value="{$Hardware.ID}">{$Hardware.Name}</option>
								{/foreach}
						</select>
						</td>
					</tr>
				</table>
			</td>
			<td>
				<table>
					{foreach from=$Location->getShipsSold() item=Ship}
						<tr>
							<td>{$Ship.Name}</td>
							<td><input type="checkbox" name="remove_ships[]" value="{$Ship.ShipTypeID}" /></td>
						</tr>
					{/foreach}
					<tr>
						<td>Add Ship:</td>
						<td>
							<select name="add_ship_id">
								<option value="0">None</option>
								{foreach from=$Ships item=Ship}
									<option value="{$Ship.ShipTypeID}">{$Ship.Name}</option>
								{/foreach}
						</select>
						</td>
					</tr>
				</table>
			</td>
			<td>
				<table>
					{foreach from=$Location->getWeaponsSold() item=Weapon}
						<tr>
							<td>{$Weapon->getName()}</td>
							<td><input type="checkbox" name="remove_weapons[]" value="{$Weapon->getWeaponTypeID()}" /></td>
						</tr>
					{/foreach}
					<tr>
						<td>Add Weapon:</td>
						<td>
							<select name="add_weapon_id">
								<option value="0">None</option>
								{foreach from=$Weapons item=Weapon}
									<option value="{$Weapon->getWeaponTypeID()}">{$Weapon->getName()}</option>
								{/foreach}
						</select>
						</td>
					</tr>
				</table>
			</td>
			<td>
				<input type="submit" name="save" value="Save"/>
			</td>
		</form>
	</tr>
{/if}
</table>