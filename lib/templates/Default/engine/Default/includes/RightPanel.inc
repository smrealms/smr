<?php
if(isset($GameID))
{
	$UnderAttack = $ThisShip->removeUnderAttack(); ?>
	<span id="attack_area"><?php if($UnderAttack) { ?><p class="attack_warning">You Are Under Attack!</p><script type="text/javascript">TriggerAttackBlink('3B1111');</script><?php } ?></span>
	<span id="message_area"><?php
	if(isset($MessageGlobalLink))
	{ ?>
		<a href="<?php echo $MessageGlobalLink; ?>"><img src="images/global_msg.gif" alt="Global Messages" /></a>
		<span class="small"><?php echo $MessageGlobalNum; ?></span><?php
	}
	if(isset($MessagePersonalLink))
	{ ?>
		<a href="<?php echo $MessagePersonalLink; ?>"><img src="images/personal_msg.gif" alt="Personal Messages" /></a>
		<span class="small"><?php echo $MessagePersonalNum; ?></span><?php
	}
	if(isset($MessageScoutLink))
	{ ?>
		<a href="<?php echo $MessageScoutLink; ?>"><img src="images/scout_msg.gif" alt="Scout Messages" /></a>
		<span class="small"><?php echo $MessageScoutNum; ?></span><?php
	}
	if(isset($MessagePoliticalLink))
	{ ?>
		<a href="<?php echo $MessagePoliticalLink; ?>"><img src="images/council_msg.gif" alt="Political Messages" /></a>
		<span class="small"><?php echo $MessagePoliticalNum; ?></span><?php
	}
	if(isset($MessageAllianceLink))
	{ ?>
		<a href="<?php echo $MessageAllianceLink; ?>"><img src="images/alliance_msg.gif" alt="Alliance Messages" /></a>
		<span class="small"><?php echo $MessageAllianceNum; ?></span><?php
	}
	if(isset($MessageAdminLink))
	{ ?>
		<a href="<?php echo $MessageAdminLink; ?>"><img src="images/admin_msg.gif" alt="Admin Messages" /></a>
		<span class="small"><?php echo $MessageAdminNum; ?></span><?php
	}
	if(isset($MessagePlanetLink))
	{ ?>
		<a href="<?php echo $MessagePlanetLink ?>"><img src="images/planet_msg.gif" alt="Planet Messages" /></a>
		<span class="small"><?php echo $MessagePlanetNum; ?></span><?php
	} 
	if(isset($MessageGlobalLink) || isset($MessagePersonalLink) || isset($MessageScoutLink) || isset($MessagePoliticalLink) || isset($MessageAllianceLink) || isset($MessageAdminLink) || isset($MessagePlanetLink))
	{
		?><br /><?php
	} ?>
	</span><?php
	echo $ThisPlayer->getLevelName(); ?><br />
	<big><a href="<?php echo $PlayerNameLink; ?>"><?php echo $ThisPlayer->getDisplayName(); ?></a></big><br /><?php
	if(isset($PlayerInvisible))
	{ ?>
		<span class="smallFont smallCaps red">INVISIBLE</span><br /><?php
	} ?>
	<br />
	Race : <?php echo $ThisPlayer->getRaceName(); ?><br />
	Turns : <span id="turns">
		<span class="<?php
			switch($ThisPlayer->getTurnsLevel())
			{
				case 'NONE':
				case 'LOW':
					echo 'red';
				break;
				case 'MEDIUM':
					echo 'yellow';
				break;
				default:
					echo 'green';
			}
			?>"><?php
				echo $ThisPlayer->getTurns() .'/'.$ThisPlayer->getMaxTurns();
			?></span>
		</span><br />
	<span id="newbieturns"><?php
		if($ThisPlayer->hasNewbieTurns())
		{
			?>Newbie Turns : <span class="<?php
			if ($ThisPlayer->getNewbieTurns() > NEWBIE_TURNS_WARNING_LIMIT) { ?>green<?php } else { ?>red<?php } ?>"><?php echo $ThisPlayer->getNewbieTurns(); ?></span><br /><?php
		} ?>
	</span>
	Credits : <?php echo number_format($ThisPlayer->getCredits()); ?><br />
	Experience : <?php echo number_format($ThisPlayer->getExperience()); ?><br />
	Level : <a href="<?php echo URL; ?>/level_requirements.php" target="levelRequirements"><?php echo $ThisPlayer->getLevelID(); ?></a><br />
	Next Level :<br /><?php
	$NextLevelExperience = number_format($ThisPlayer->getNextLevelExperience());
	$Experience = number_format($ThisPlayer->getExperience()); ?>
	<img src="images/bar_left.gif" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
	<img src="images/blue.gif" style="width:<?php echo $ThisPlayer->getNextLevelPercentAcquired(); ?>px;height:10px;" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
	<img src="images/bar_border.gif" style="width:<?php echo $ThisPlayer->getNextLevelPercentRemaining(); ?>px;height:10px;" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
	<img src="images/bar_right.gif" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" /><br />
	Alignment : <?php echo get_colored_text($ThisPlayer->getAlignment(),number_format($ThisPlayer->getAlignment())); ?><br />
	Alliance : <a href="<?php echo $AllianceLink; ?>"><?php
		echo $ThisPlayer->getAllianceName();
		if($ThisPlayer->hasAlliance())
		{
			echo '('.number_format($ThisPlayer->getAllianceID()).')';
		} ?></a><br />
	<br />
	<a href="<?php echo URL; ?>/ship_list.php" target="shipList"><span id="ship_name"><?php /*<a href="<?php echo $ThisShip->getUpgradeShipHREF(); ?>">*/ ?><span class="yellow bold"><?php echo $ThisShip->getName(); ?></span><?php /*</a>*/ ?></span></a><br />
	<?php
	if($ThisPlayer->hasCustomShipName())
	{
		echo $ThisPlayer->getCustomShipName(); ?><br /><?php
	} ?>
	Rating : <span id="offense"><?php echo number_format($ThisShip->getAttackRating()); ?></span>/<span id="defense"><?php echo number_format($ThisShip->getDefenseRating()); ?></span><br />
	Shields : <span id="shields"><?php echo $ThisShip->getShields(); ?>/<?php echo $ThisShip->getMaxShields(); ?></span><br />
	Armour : <span id="armour"><?php echo $ThisShip->getArmour(); ?>/<?php echo $ThisShip->getMaxArmour(); ?></span><br />
	<a href="<?php echo $HardwareLink; ?>">CIJSD</a> : <?php
	if($ThisShip->hasCloak()) echo '*';
	else echo '-';
	if($ThisShip->hasIllusion()) echo '*';
	else echo '-';
	if($ThisShip->hasJump()) echo '*';
	else echo '-';
	if($ThisShip->hasScanner()) echo '*';
	else echo '-';
	if($ThisShip->hasDCS()) echo '*';
	else echo '-';
	?><br /><?php
	if($ThisShip->hasCloak())
	{
		if($ThisShip->isCloaked())
		{
			?><strong style="color:lime;">*** Cloak active ***</strong><?php
		}
		else
		{
			?><strong style="color:red;">*** Cloak inactive ***</strong><?php
		} ?>
		<br /><br /><?php
	}
	if($ThisShip->hasActiveIllusion())
	{ ?>
		<span class="npcColor"><?php echo $ThisShip->getIllusionShipName(); ?></span><br />
		IG Rating : <?php echo $ThisShip->getIllusionAttack(); ?>/<?php echo $ThisShip->getIllusionDefense(); ?><br />
		<br /><?php
	} ?>
	<br />
	<a href="<?php echo $ForceDropLink; ?>"><span class="bold">Forces</span></a><br /><?php
	if(isset($DropMineLink))
	{ ?>
		<a href="<?php echo $DropMineLink; ?>">
			<span class="bold">[X]</span>
		</a><?php
	} ?>Mines : <?php echo $ThisShip->getMines(); ?>/<?php echo $ThisShip->getMaxMines(); ?><br /><?php
	
	if(isset($DropCDLink))
	{ ?>
		<a href="<?php echo $DropCDLink; ?>">
			<span class="bold">[X]</span>
		</a><?php
	} ?>Combat : <?php echo $ThisShip->getCDs(); ?>/<?php echo $ThisShip->getMaxCDs(); ?><br /><?php
	
	if(isset($DropSDLink))
	{ ?>
		<a href="<?php echo $DropSDLink; ?>">
			<span class="bold">[X]</span>
		</a><?php
	} ?>Scout : <?php echo $ThisShip->getSDs(); ?>/<?php echo $ThisShip->getMaxSDs(); ?><br />
	<br />
	<a href="<?php echo $CargoJettisonLink; ?>"><span class="bold">Cargo Holds (<?php echo $ThisShip->getCargoHolds(); ?>/<?php echo $ThisShip->getMaxCargoHolds(); ?>)</span></a><br />
	<?php
	if($ThisShip->hasCargo())
	{
		foreach($ThisShip->getCargo() as $GoodID => $GoodAmount)
		{
			$Good =& Globals::getGood($GoodID); ?>
			<img src="<?php echo $Good['ImageLink']; ?>" alt="<?php echo $Good['Name']; ?>"> : <?php echo $GoodAmount; ?><br /><?php
		}
	} ?>
	Empty : <?php echo $ThisShip->getEmptyHolds(); ?><br /><br />
	<a href="<?php echo $WeaponReorderLink; ?>"><span class="bold">Weapons</span></a><br /><?php
	if($ThisShip->hasWeapons())
	{ ?>
		<div class="wep_drop1" onclick="toggleWepD(<?php echo $ThisShip->getNumWeapons(); ?>,'<?php echo $ThisPlayer->getToggleWeaponHidingHREF(); ?>');">
			<noscript><a href="<?php echo $ThisPlayer->getToggleWeaponHidingHREF(); ?>"></noscript>
				Show/Hide (<?php echo $ThisShip->getNumWeapons(); ?>)<br /><?php
					$i=0;
					$Weapons =& $ThisShip->getWeapons();
					foreach($Weapons as &$Weapon)
					{
						$i++; ?>
						<span class="wep1" <?php
							if(!$ThisPlayer->isDisplayWeapons()){ ?>style="display: none;"<?php } ?> id="wep_item<?php echo $i; ?>"><?php echo $Weapon->getName(); ?><br />
						</span><?php
					} unset($Weapon); unset($Weapons); ?>
			<noscript></a></noscript>
		</div><?php
	} ?>
	Open : <?php echo $ThisShip->getOpenWeaponSlots(); ?><br />Total Damage: (<?php echo $ThisShip->getTotalShieldDamage(); ?>/<?php echo $ThisShip->getTotalArmourDamage(); ?>)<br />
	Power Used: <?php echo $ThisShip->getPowerUsed(); ?>/<?php echo $ThisShip->getMaxPower(); ?><br /><br /><?php 
	if(ENABLE_BETA)
	{
		?><a href="<?php echo Globals::getBetaFunctionsHREF(); ?>"><span class="bold">Beta Functions</span></a><?php
	}
} ?>