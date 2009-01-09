{foreach from=$ForcesCombatResults.Results key=ForceType item=ForceResults}
	{assign var=ShootingWeapon value=$ForceResults.Weapon}
	{assign var=ShotHit value=$ForceResults.Hit}
	{assign var=ActualDamage value=$ForceResults.ActualDamage}
	{assign var=WeaponDamage value=$ForceResults.WeaponDamage}
	{assign var=TargetPlayer value=$ForceResults.TargetPlayer}
	{assign var=DamageTypes value=0}
	{if $ActualDamage.Shield > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
	{if $ActualDamage.NumCDs > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
	{if $ActualDamage.Armour > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
	
	<span style="color:yellow;">{$ActualDamage.Launched}</span>
	{if $ForceType == 'Mines'}
		 mines kamikaze themselves against
	{elseif $ForceType == 'Drones'}
		 combat drones launch at
	{elseif $ForceType == 'Scouts'}
		 scout drones kamikaze themselves against
	{/if}
	
	{if $ShotHit && $ActualDamage.TargetAlreadyDead} the debris that was once{/if} {$TargetPlayer->getName()}{*
	*}{if !$ActualDamage.TargetAlreadyDead} {*
		*}{if !$ShotHit} and misses{elseif $ActualDamage.TotalDamage == 0}{*
			*}{if $WeaponDamage.Shield > 0}{if $ActualDamage.HasCDs}which proves ineffective against their combat drones{else}which washes harmlessly over their hull{/if}{*
			*}{elseif $WeaponDamage.Armour > 0}which is deflected by their shields{else}but it cannot do any damage{/if}{*
			*}{else}destroying {if $ActualDamage.Shield > 0}<span class="cyan">{$ActualDamage.Shield|number_format}</span> shields{if $DamageTypes == 3}, {elseif $DamageTypes == 2} and {/if}{assign var=DamageTypes value=$DamageTypes-1}{/if}{*
				*}{if $ActualDamage.NumCDs > 0}<span class="yellow">{$ActualDamage.NumCDs|number_format}</span> drones{if $DamageTypes == 2} and {/if}{assign var=DamageTypes value=$DamageTypes-1}{/if}{*
				*}{if $ActualDamage.Armour > 0}<span class="red">{$ActualDamage.Armour|number_format}</span> plates of armour{/if}{*
	*}{/if}{/if}.
	<br />
{/foreach}
{if $ForcesCombatResults.ForcesDestroyed}Forces are <span style="color:red;">DESTROYED!</span><br />{/if}

{assign var=TotalDamage value=$ForcesCombatResults.TotalDamage}
The forces {if $TotalDamage > 0}hit for a total of <span class="red">{$TotalDamage}</span> damage in this round of combat{else}do no damage this round.{/if}.