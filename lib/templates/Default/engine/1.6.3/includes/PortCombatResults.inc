<?php
$CombatPort =& $PortCombatResults['Port'];
if(is_array($PortCombatResults['Weapons']))
{
	foreach($PortCombatResults['Weapons'] as $WeaponResults)
	{
		$ShootingWeapon =& $WeaponResults['Weapon'];
		$ShotHit =& $WeaponResults['Hit'];
		$ActualDamage =& $WeaponResults['ActualDamage'];
		$WeaponDamage =& $WeaponResults['WeaponDamage'];
		$TargetPlayer =& $WeaponResults['TargetPlayer'];
		$DamageTypes = 0;
		if($ActualDamage['Shield'] > 0){ $DamageTypes = $DamageTypes+1; }
		if($ActualDamage['NumCDs'] > 0){ $DamageTypes = $DamageTypes+1; }
		if($ActualDamage['Armour'] > 0){ $DamageTypes = $DamageTypes+1; }
		
		echo $CombatPort->getDisplayName() ?> fires a <?php echo $ShootingWeapon->getName() ?> at <?php if($ShotHit && $ActualDamage['TargetAlreadyDead']){ ?> the debris that was once <?php } echo $TargetPlayer->getDisplayName();
		if(!$ActualDamage['TargetAlreadyDead'])
		{
			if(!$ShotHit)
			{
				?> and misses<?php
			}
			else if($ActualDamage['TotalDamage'] == 0)
			{
				if($WeaponDamage['Shield'] > 0)
				{
					if($ActualDamage['HasCDs'])
					{
						?> which proves ineffective against their combat drones<?php
					}
					else
					{
						?> which washes harmlessly over their hull<?php
					}
				}
				else if($WeaponDamage['Armour'] > 0)
				{
					?> which is deflected by their shields<?php
				}
				else
				{
					?> but it cannot do any damage<?php
				}
			}
			else
			{
				?> destroying <?php
				if($ActualDamage['Shield'] > 0)
				{
					?><span class="cyan"><?php echo number_format($ActualDamage['Shield']) ?></span> shields<?php
					$this->doDamageTypeReductionDisplay($DamageTypes);
				}
				if($ActualDamage['NumCDs'] > 0)
				{
					?><span class="yellow"><?php echo number_format($ActualDamage['NumCDs']) ?></span> combat drones<?php
					$this->doDamageTypeReductionDisplay($DamageTypes);
				}
				if($ActualDamage['Armour'] > 0)
				{
					?><span class="red"><?php echo number_format($ActualDamage['Armour']) ?></span> plates of armour<?php
				}
			}
		} ?>.
		<br />
		<?php if($ActualDamage['KillingShot'])
		{
			$this->includeTemplate('includes/TraderCombatKillMessage.inc',array('TargetPlayer'=>&$TargetPlayer));
		}
	}
}
if(isset($PortCombatResults['Drones']))
{
	$Drones =& $PortCombatResults['Drones'];
	$ActualDamage =& $Drones['ActualDamage'];
	$WeaponDamage =& $Drones['WeaponDamage'];
	$TargetPlayer =& $Drones['TargetPlayer'];
	$DamageTypes = 0;
	if($ActualDamage['Shield'] > 0){ $DamageTypes = $DamageTypes+1; }
	if($ActualDamage['NumCDs'] > 0){ $DamageTypes = $DamageTypes+1; }
	if($ActualDamage['Armour'] > 0){ $DamageTypes = $DamageTypes+1; }
	
	echo $CombatPort->getDisplayName();
	if($WeaponDamage['Launched'] == 0)
	{
		?> fails to launch it's combat drones<?php
	}
	else
	{
		?> launches <span class="yellow"><?php echo $WeaponDamage['Launched'] ?></span> combat drones at <?php if($ActualDamage['TargetAlreadyDead']){ ?>the debris that was once <?php } echo $TargetPlayer->getDisplayName();
		if(!$ActualDamage['TargetAlreadyDead'])
		{
			if($ActualDamage['TotalDamage'] == 0)
			{
				if($WeaponDamage['Shield'] > 0)
				{
					if($ActualDamage['HasCDs'])
					{
						?> which prove ineffective against their combat drones<?php
					}
					else
					{
						?> which washes harmlessly over their hull<?php
					}
				}
				else if($WeaponDamage['Armour'] > 0)
				{
					?> which is deflected by their shields<?php
				}
				else
				{
					?> but they cannot do any damage<?php
				}
			}
			else
			{
				?> destroying <?php
				if($ActualDamage['Shield'] > 0)
				{
					?><span class="cyan"><?php echo number_format($ActualDamage['Shield']) ?></span> shields<?php
					$this->doDamageTypeReductionDisplay($DamageTypes);
				}
				if($ActualDamage['NumCDs'] > 0)
				{
					?><span class="yellow"><?php echo number_format($ActualDamage['NumCDs']) ?></span> combat drones<?php
					$this->doDamageTypeReductionDisplay($DamageTypes);
				}
				if($ActualDamage['Armour'] > 0)
				{
					?><span class="red"><?php echo number_format($ActualDamage['Armour']) ?></span> plates of armour<?php
				}
			}
		}
	} ?>.
	<br />
	<?php if($ActualDamage['KillingShot'])
	{
		$this->includeTemplate('includes/TraderCombatKillMessage.inc',array('TargetPlayer'=>&$TargetPlayer));
	}
}

$TotalDamage = $PortCombatResults['TotalDamage'];
echo $CombatPort->getDisplayName();
	if($TotalDamage > 0)
	{
		?> hit for a total of <span class="red"><?php echo $TotalDamage ?></span> damage in this round of combat<?php
	}
	else
	{
		?> does no damage this round<?php
	} ?>.