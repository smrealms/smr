<form action="{$EditDummysLink}" method="GET">
	Edit Dummy:
	<select name="dummy_name">
		{foreach from=$DummyNames item=DummyName}
			<option value="{$DummyName}"{if $DummyName==$DummyPlayer->getPlayerName()} selected="selected"{/if}>{$DummyName}</option>
		{/foreach}
	</select><br />
	<input type="submit" value="Select Dummy" />
</form>

<table>
	<tr>
		<td style="vertical-align:top">
			<u>{$DummyPlayer->getPlayerName()}</u><br/><br />
			<form action="{$EditDummysLink}" method="POST">
				<input type="text" name="dummy_name" value="{$DummyPlayer->getPlayerName()}" />
				Level
				<select name="level">
					{foreach from=$Levels item=Level}
						<option value="{$Level.Requirement}"{if $Level.ID==$DummyPlayer->getLevelID()} selected="selected"{/if}>{$Level.ID}</option>
					{/foreach}
				</select>
				Ship:
				<select name="ship_id">
					{foreach from=$BaseShips item=BaseShip}
						<option value="{$BaseShip.ShipTypeID}"{if $BaseShip.ShipTypeID==$DummyPlayer->getShipTypeID()} selected="selected"{/if}>{$BaseShip.Name}</option>
					{/foreach}
				</select>
				&nbsp;DCS&nbsp;<input type="checkbox" name="DCS" /><br />
				
				{foreach from=$ShipWeapons key=OrderID item=ShipWeapon}
					Weapon: {$OrderID}
					<select name="weapon[]">
						{foreach from=$Weapons item=Weapon}
							<option value="{$Weapon->getWeaponTypeID()}"{if $Weapon->getWeaponTypeID()==$ShipWeapon->getWeaponTypeID()} selected="selected"{/if}>{$Weapon->getName()} (dmg: {$Weapon->getShieldDamage()}/{$Weapon->getArmourDamage()} acc: {$Weapon->getBaseAccuracy()}% lvl:{$Weapon->getPowerLevel()})</option>
						{/foreach}
				{/foreach}
				<input type="submit" name="save_dummy" value="Save Dummy" />
			</form>
		</td>
		<td style="vertical-align:top">
			<u>Current Details</u><br/><br/>Level: 0<br />Ship: Galactic Semi<br />DCS: false<br/>Weapons:<br/>Newbie Pulse Laser<br />
		</td>
	</tr>
</table>