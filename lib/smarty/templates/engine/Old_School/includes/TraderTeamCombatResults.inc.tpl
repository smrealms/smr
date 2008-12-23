{foreach from=$TraderTeamCombatResults.Traders key=AccountID item=TraderResults}
	{assign var=ShootingPlayer value=$TraderResults.Player}
	{assign var=TotalDamage value=$TraderResults.TotalDamage}
	{foreach from=$TraderResults.Weapons item=WeaponResults}
		{assign var=ShootingWeapon value=$WeaponResults.Weapon}
		{assign var=ShotHit value=$WeaponResults.Hit}
		{assign var=ActualDamage value=$WeaponResults.ActualDamage}
		{assign var=WeaponDamage value=$WeaponResults.WeaponDamage}
		{assign var=TargetPlayer value=$WeaponResults.TargetPlayer}
		{assign var=DamageTypes value=0}
		{if $ActualDamage.Shield > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
		{if $ActualDamage.NumCDs > 0}{assign var=DamageTypes value=$DamageTypes+1}{/if}
		{if $ActualDamage.Armour > 0}{assign var=DamageTypes value=`$DamageTypes+1}{/if}
		
		{$ShootingPlayer->getName()} fires their {$ShootingWeapon->getName()} at{if $ShotHit && $ActualDamage.TargetAlreadyDead} the debris that was once{/if} {$TargetPlayer->getName()} {*
		*}{if !$ActualDamage.TargetAlreadyDead}{*
			*}{if !$ShotHit}and misses{elseif $ActualDamage.TotalDamage == 0}{*
				*}{if $WeaponDamage.Shield > 0}{if $ActualDamage.HasCDs}which proves ineffective against their combat drones{else}which washes harmlessly over their hull{/if}{*
				*}{elseif $WeaponDamage.Armour > 0}which is deflected by their shields{else}but it cannot do any damage{/if}{*
				*}{else}destroying {if $ActualDamage.Shield > 0}<span class="cyan">{$ActualDamage.Shield|number_format}</span> shields{if DamageTypes == 3}, {elseif DamageTypes == 2} and {/if}{assign var=DamageTypes value=$DamageTypes-1}{*
					*}{elseif $ActualDamage.NumCDs > 0}<span class="yellow">{$ActualDamage.NumCDs|number_format}</span> drones{if DamageTypes == 2} and {/if}{assign var=DamageTypes value=$DamageTypes-1}{*
					*}{elseif $ActualDamage.Armour > 0}<span class="red">{$ActualDamage.Armour|number_format}</span> plates of armour{/if}{*
		*}{/if}{/if}.
		{if $ActualDamage.KillingShot}<br />{assign var=KillResults value=$WeaponResults.KillResults}
		{$TargetPlayer->getName()} has been <span class="red">DESTROYED!</span><br />
		{$ShootingPlayer->getName()} salvages <span class="yellow">{$KillResults.KillerCredits|number_format}</span> credits from the wreckage and gains <span class="blue">{$KillResults.KillerExp|number_format}</span> experience.{/if}
		<br />
	{foreachelse}
		{if $TraderResults.DeadBeforeShot}{$ShootingPlayer->getName()} died before they were able to fire their weapons!{/if}
		<br />
	{/foreach}
	{$ShootingPlayer->getName()} {if $TotalDamage > 0}hits for a total of <span class="red">{$TotalDamage}</span> damage in this round of combat{else}does no damage this round.{if !$TraderResults.DeadBeforeShot} Maybe they should go back to the academy{/if}{/if}.<br /><br />
{/foreach}
{assign var=TotalDamage value=$TraderTeamCombatResults.TotalDamage}
This fleet {if $TotalDamage > 0}hits for a total of <span class="red">{$TotalDamage}</span> damage in this round of combat{else}does no damage this round. You call that a fleet? They need a better recruiter{/if}.
{*

function build_results(&$players,&$fleets,&$weapons,&$killed_ids,&$killer_ids) {
	for($i=0;$i<2;++$i) {
		$fleet_damage = 0;
		
		foreach($fleets[$i] as $attacker) {
			$total_damage = 0;
			$weapon = 0;
			foreach($players[$attacker][RESULTS] as $result) {
				$total_damage += ($result[0] + $result[1] + $result[2]);
				$results .=  $players[$attacker][PLAYER_NAME];
				if(!$players[$attacker][WEAPONS][$weapon]) {
					if($result[3]) {
						$results .= ' launches <span class="yellow">' . $result[3] . '</span> drones';
					}
					else {
						$results .= ' fails to launch their drones';
					}
			}
	
		}
	}
	
	return $results;
*}