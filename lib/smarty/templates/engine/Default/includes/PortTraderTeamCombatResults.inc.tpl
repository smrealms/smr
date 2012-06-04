{foreach from=$TraderTeamCombatResults.Traders key=AccountID item=TraderResults}
	{assign var=ShootingPlayer value=$TraderResults.Player}
	{assign var=TotalDamage value=$TraderResults.TotalDamage}
	{if $TraderResults.DeadBeforeShot}
		{$ShootingPlayer->getDisplayName()} died before they were able to attack!<br />
	{else}
		{foreach from=$TraderResults.Weapons item=WeaponResults}
			{assign var=ShootingWeapon value=$WeaponResults.Weapon}
			{assign var=ShotHit value=$WeaponResults.Hit}
			{assign var=ActualDamage value=$WeaponResults.ActualDamage}
			{assign var=WeaponDamage value=$WeaponResults.WeaponDamage}
			{assign var=TargetPort value=$WeaponResults.TargetPort}
			{assign var=DamageTypes value=0}
			{if $ActualDamage.Shield > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			{if $ActualDamage.NumCDs > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			{if $ActualDamage.Armour > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			
			{$ShootingPlayer->getDisplayName()} fires their {$ShootingWeapon->getName()} at{if $ShotHit && $ActualDamage.TargetAlreadyDead} the debris that was once{/if} {$TargetPort->getDisplayName()}{*
			*}{if !$ActualDamage.TargetAlreadyDead} {*
				*}{if !$ShotHit}and misses{elseif $ActualDamage.TotalDamage == 0}{*
					*}{if $WeaponDamage.Shield > 0}{if $ActualDamage.HasCDs}which proves ineffective against their combat drones{else}which washes harmlessly over their hull{/if}{*
					*}{elseif $WeaponDamage.Armour > 0}which is deflected by their shields{else}but it cannot do any damage{/if}{*
					*}{else}destroying {if $ActualDamage.Shield > 0}<span class="cyan">{$ActualDamage.Shield|number_format}</span> shields{if $DamageTypes == 3}, {elseif $DamageTypes == 2} and {/if}{assign var=DamageTypes value=$DamageTypes-1}{/if}{*
						*}{if $ActualDamage.NumCDs > 0}<span class="yellow">{$ActualDamage.NumCDs|number_format}</span> combat drones{if $DamageTypes == 2} and {/if}{assign var=DamageTypes value=$DamageTypes-1}{/if}{*
						*}{if $ActualDamage.Armour > 0}<span class="red">{$ActualDamage.Armour|number_format}</span> plates of armour{/if}{*
			*}{/if}{/if}.
			<br />
			{if $ActualDamage.KillingShot}
				{include_template template="includes/PortKillMessage.inc" assign=Template}{include file=$Template KillResults=$WeaponResults.KillResults}
			{/if}
		{/foreach}
		{if isset($TraderResults.Drones)}
			{assign var=Drones value=$TraderResults.Drones}
			{assign var=WeaponDamage value=$Drones.WeaponDamage}
			{assign var=ActualDamage value=$Drones.ActualDamage}
			{assign var=TargetPort value=$Drones.TargetPort}
			{assign var=DamageTypes value=0}
			{if $ActualDamage.Shield > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			{if $ActualDamage.NumCDs > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			{if $ActualDamage.Armour > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			{$ShootingPlayer->getDisplayName()} {if $WeaponDamage.Launched == 0}fails to launch their combat drones{else}launches <span class="yellow">{$WeaponDamage.Launched}</span> combat drones at{if $ActualDamage.TargetAlreadyDead} the debris that was once{/if} {$TargetPort->getDisplayName()}{*
				*}{if !$ActualDamage.TargetAlreadyDead} {*
					*}{if $ActualDamage.TotalDamage == 0}{*
						*}{if $WeaponDamage.Shield > 0}{if $ActualDamage.HasCDs}which prove ineffective against their combat drones{else}which washes harmlessly over their hull{/if}{*
						*}{elseif $WeaponDamage.Armour > 0}which is deflected by their shields{else}but they cannot do any damage{/if}{*
					*}{else}destroying {if $ActualDamage.Shield > 0}<span class="cyan">{$ActualDamage.Shield|number_format}</span> shields{if $DamageTypes == 3}, {elseif $DamageTypes == 2} and {/if}{assign var=DamageTypes value=$DamageTypes-1}{/if}{*
							*}{if $ActualDamage.NumCDs > 0}<span class="yellow">{$ActualDamage.NumCDs|number_format}</span> combat drones{if $DamageTypes == 2} and {/if}{assign var=DamageTypes value=$DamageTypes-1}{/if}{*
							*}{if $ActualDamage.Armour > 0}<span class="red">{$ActualDamage.Armour|number_format}</span> plates of armour{/if}{*
					*}{/if}{*
				*}{/if}{*
			*}{/if}.
			<br />
			{if $ActualDamage.KillingShot}
				{include_template template="includes/PortKillMessage.inc" assign=Template}{include file=$Template KillResults=$Drones.KillResults}
			{/if}
		{/if}
	{/if}
	{$ShootingPlayer->getDisplayName()} {if $TotalDamage > 0}hits for a total of <span class="red">{$TotalDamage}</span> damage in this round of combat{else}does no damage this round.{if !$TraderResults.DeadBeforeShot} Maybe they should go back to the academy{/if}{/if}.<br /><br />
{/foreach}
{assign var=TotalDamage value=$TraderTeamCombatResults.TotalDamage}
This fleet {if $TotalDamage > 0}hits for a total of <span class="red">{$TotalDamage}</span> damage in this round of combat{else}does no damage this round. You call that a fleet? They need a better recruiter{/if}.