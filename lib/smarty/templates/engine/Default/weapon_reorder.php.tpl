{if $ThisShip->hasWeapons()}
	<table cellspacing="0" cellpadding="3" border="0" class="standard">
	<tr>
	<th align="center">Weapon Name</th>
	<th align="center">Shield Damage</th>
	<th align="center">Armor Damage</th>
	<th align="center">Power Level</th>
	<th align="center">Accuracy</th>
	<th align="center">Action</th>
	</tr>
	{foreach from=$ThisShip->getWeapons() key=OrderID item=Weapon}
		<tr>
			<td>{$Weapon->getName()}</td>
			<td align="center">{$Weapon->getShieldDamage()}</td>
			<td align="center">{$Weapon->getArmourDamage()}</td>
			<td>{$Weapon->getPowerLevel()}</td>
			<td>{$Weapon->getBaseAccuracy()}</td>
			<td>
				<a href="{$Globals->getWeaponReorderHREF($OrderID,'Up')}">
					{if $OrderID > 1}
						<img src="images/up.gif" alt="Switch up" title="Switch up">
					{else}
						<img src="images/up_push.gif" alt="Push up" title="Push up">
					{/if}
				</a>
				<a href="{$Globals->getWeaponReorderHREF($OrderID,'Down')}">
					{if $OrderID < $ThisShip->getNumWeapons()}
						<img src="images/down.gif" alt="Switch down" title="Switch down">
					{else}
						<img src="images/down_push.gif" alt="Push down" title="Push down">
					{/if}
				</a>
			</td>
		</tr>
	{/foreach}
	</table>
{else}
	You don't have any weapons!
{/if}