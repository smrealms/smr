<table>
	{foreach from=$Team key=OrderID item=Dummy}
	<tr>
		<td style="vertical-align:top">
			<u>{$MemberDescription} {$OrderID}</u><br /><br />
		</td>
		<td style="vertical-align:top">
			<select name="{$MemberInputName}[]">
				<option value="none">None</option>
				{foreach from=$DummyNames item=DummyName}
					<option value="{$DummyName}"{if $Dummy && $DummyName==$Dummy->getPlayerName()} selected="selected"{/if}>{$DummyName}</option>
				{/foreach}
			</select><br />
		</td>
		<td style="vertical-align:top">
			<u>Current Details</u>
				{if $Dummy}{assign var=Ship value=$Dummy->getShip()}{assign var=ShipWeapons value=$Ship->getWeapons()}
					<br />Level: {$Dummy->getLevelID()}<br />
					Ship: {$Ship->getName()} ({$Ship->getAttackRating()}/{$Ship->getDefenseRating()})<br />
					DCS: {if $Ship->hasDCS()}Yes{else}No{/if}<br />
					Weapons: {foreach from=$ShipWeapons item=ShipWeapon}* {$ShipWeapon->getName()}<br />{/foreach}
				{else}No Dummy{/if}
		</td>
	</tr>
	{/foreach}
</table>