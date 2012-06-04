{foreach from=$TraderTeamCombatResults.Traders key=AccountID item=TraderResults}
	{assign var=ShootingPlayer value=$TraderResults.Player}
	{assign var=TotalDamage value=$TraderResults.TotalDamage}
	{if $TraderResults.DeadBeforeShot}
		{$ShootingPlayer->getName()} died before they were able to attack!<br />
	{else}
		{foreach from=$TraderResults.Weapons item=WeaponResults}
			{assign var=ShootingWeapon value=$WeaponResults.Weapon}
			{assign var=ShotHit value=$WeaponResults.Hit}
			{assign var=ActualDamage value=$WeaponResults.ActualDamage}
			{assign var=WeaponDamage value=$WeaponResults.WeaponDamage}
			{assign var=TargetPlayer value=$WeaponResults.TargetPlayer}
			{assign var=DamageTypes value=0}
			{if $ActualDamage.NumMines > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			{if $ActualDamage.NumCDs > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			{if $ActualDamage.NumSDs > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			
			{$ShootingPlayer->getName()} fires their {$ShootingWeapon->getName()} at{if $ShotHit && $ActualDamage.TargetAlreadyDead} the debris that was once{/if} the forces{*
			*}{if !$ActualDamage.TargetAlreadyDead} {*
				*}{if !$ShotHit}and misses{elseif $ActualDamage.TotalDamage == 0}{*
					*}{if $WeaponDamage.Shield > 0}which proves ineffective against the {if $ActualDamage.HasMines}mines{elseif $ActualDamage.HasCDs}combat drones{else}scout drones{/if}{*
					*}{elseif $WeaponDamage.Armour > 0}which is deflected by the {if $ActualDamage.HasMines}mines{elseif $ActualDamage.HasCDs}combat drones{else}scout drones{/if} shields{*
					*}{else}but it cannot do any damage{/if}{*
				*}{else}destroying {if $ActualDamage.NumMines > 0}<span class="red">{$ActualDamage.NumMines|number_format}</span> mines{if $DamageTypes == 3}, {elseif $DamageTypes == 2} and {/if}{assign var=DamageTypes value=$DamageTypes-1}{/if}{*
					*}{if $ActualDamage.NumCDs > 0}<span class="red">{$ActualDamage.NumCDs|number_format}</span> combat drones{if $DamageTypes == 2} and {/if}{assign var=DamageTypes value=$DamageTypes-1}{/if}{*
					*}{if $ActualDamage.NumSDs > 0}<span class="red">{$ActualDamage.NumSDs|number_format}</span> scout drones{/if}{*
			*}{/if}{/if}.
			<br />
			{if $ActualDamage.KillingShot}
				Forces are <span style="color:red;">DESTROYED!</span><br />
			{/if}
		{/foreach}
		{if isset($TraderResults.Drones)}
			{assign var=Drones value=$TraderResults.Drones}
			{assign var=WeaponDamage value=$Drones.WeaponDamage}
			{assign var=ActualDamage value=$Drones.ActualDamage}
			{assign var=TargetPlayer value=$Drones.TargetPlayer}
			{assign var=DamageTypes value=0}
			{if $ActualDamage.NumMines > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			{if $ActualDamage.NumCDs > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			{if $ActualDamage.NumSDs > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
			{$ShootingPlayer->getName()} {if $WeaponDamage.Launched == 0}fails to launch their combat drones{else}launches <span class="yellow">{$WeaponDamage.Launched}</span> combat drones at{if $ActualDamage.TargetAlreadyDead} the debris that was once{/if} the forces{*
				*}{if !$ActualDamage.TargetAlreadyDead} {*
					*}{if $ActualDamage.TotalDamage == 0}{*
						*}{if $WeaponDamage.Shield > 0}which prove ineffective against the {if $ActualDamage.HasMines}mines{elseif $ActualDamage.HasCDs}combat drones{else}scout drones{/if}{*
						*}{elseif $WeaponDamage.Armour > 0}which is deflected by the {if $ActualDamage.HasMines}mines{elseif $ActualDamage.HasCDs}combat drones{else}scout drones{/if} shields{*
					*}{else}but they cannot do any damage{/if}{*
				*}{else}{if $WeaponDamage.Kamikaze == 0}destroying{else}of which <span class="yellow">{$WeaponDamage.Kamikaze}</span> kamikaze against <span class="red">{$WeaponDamage.Kamikaze}</span> mines{if $WeaponDamage.Kamikaze != $ActualDamage.NumMines} whilst the others destroy{/if}{/if} {if $WeaponDamage.Kamikaze != $ActualDamage.NumMines && $ActualDamage.NumMines > 0}<span class="red">{$ActualDamage.NumMines|number_format}</span> mines{if $WeaponDamage.Kamikaze != $ActualDamage.NumMines && $ActualDamage.NumMines > 0}{if $DamageTypes == 3}, {elseif $DamageTypes == 2} and {/if}{elseif $DamageTypes > 1} whilst the others destroy {/if}{assign var=DamageTypes value=$DamageTypes-1}{/if}{*
						*}{if $ActualDamage.NumCDs > 0}<span class="red">{$ActualDamage.NumCDs|number_format}</span> combat drones{if $DamageTypes == 2} and {/if}{assign var=DamageTypes value=$DamageTypes-1}{/if}{*
						*}{if $ActualDamage.NumSDs > 0}<span class="red">{$ActualDamage.NumSDs|number_format}</span> scout drones{/if}{*
					*}{/if}{*
				*}{/if}{*
			*}{/if}.
			<br />
			{if $ActualDamage.KillingShot}
				Forces are <span style="color:red;">DESTROYED!</span><br />
			{/if}
		{/if}
	{/if}
	{$ShootingPlayer->getName()} {if $TotalDamage > 0}hits for a total of <span class="red">{$TotalDamage}</span> damage in this round of combat{else}does no damage this round.{if !$TraderResults.DeadBeforeShot} Maybe they should go back to the academy{/if}{/if}.<br /><br />
{/foreach}
{assign var=TotalDamage value=$TraderTeamCombatResults.TotalDamage}
This fleet {if $TotalDamage > 0}hits for a total of <span class="red">{$TotalDamage}</span> damage in this round of combat{else}does no damage this round. You call that a fleet? They need a better recruiter{/if}.