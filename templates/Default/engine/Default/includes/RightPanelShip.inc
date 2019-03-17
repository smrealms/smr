<?php
if(isset($GameID)) { ?>
	<span id="flagship"><?php
	if ($ThisPlayer->isFlagship()) {
		?><img title="Alliance Flagship" alt="Alliance Flagship" src="images/flagship.png" width="16" height="12" />&nbsp;<span style="color:#06F">Flagship</span><br /><?php
	}
	?></span>
	<a href="ship_list.php" target="shipList">
		<span id="ship_name">
			<span class="yellow bold"><?php echo $ThisShip->getName(); ?></span>
		</span>
	</a><br />
	<?php
	if($ThisPlayer->hasCustomShipName()) {
		echo $ThisPlayer->getCustomShipName(); ?><br /><?php
	} ?>
	Rating : <span id="offense"><?php echo get_colored_text_range($ThisShip->getAttackRating() <= $ThisPlayer->getSafeAttackRating() ? 1 : 0, 1, number_format($ThisShip->getAttackRating())); ?></span>/<span id="defense"><?php echo get_colored_text_range($ThisShip->getDefenseRating(), $ThisShip->getMaxDefenseRating()); ?></span><br />
	Shields : <span id="shields"><?php echo get_colored_text_range($ThisShip->getShields(),$ThisShip->getMaxShields(),number_format($ThisShip->getShields()).'/'.number_format($ThisShip->getMaxShields())); ?></span><br />
	Armour : <span id="armour"><?php echo get_colored_text_range($ThisShip->getArmour(),$ThisShip->getMaxArmour(),number_format($ThisShip->getArmour()).'/'.number_format($ThisShip->getMaxArmour())); ?></span><br />
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
	?><br /><br /><?php
	if($ThisShip->hasCloak()) { ?>
		<a href="<?php echo $HardwareLink; ?>"><?php
			if($ThisShip->isCloaked()) {
				?><strong class="green">*** Cloak active ***</strong><?php
			}
			else {
				?><strong class="red">*** Cloak inactive ***</strong><?php
			} ?>
		</a><br /><br /><?php
	}
	if($ThisShip->hasActiveIllusion()) { ?>
		<a href="<?php echo $HardwareLink; ?>">
			<span class="npcColour"><?php echo $ThisShip->getIllusionShipName(); ?></span><br />
			IG Rating : <?php echo $ThisShip->getIllusionAttack(); ?>/<?php echo $ThisShip->getIllusionDefense(); ?>
		</a><br />
		<br /><?php
	} ?>
	<a href="<?php echo $ForceDropLink; ?>"><span class="bold">Forces</span></a><br /><?php
	if(isset($DropMineLink)) { ?>
		<a href="<?php echo $DropMineLink; ?>">
			<span class="bold">[X]</span>
		</a><?php
	} ?>Mines : <?php echo $ThisShip->getMines(); ?>/<?php echo $ThisShip->getMaxMines(); ?><br /><?php
	
	if(isset($DropCDLink)) { ?>
		<a href="<?php echo $DropCDLink; ?>">
			<span class="bold">[X]</span>
		</a><?php
	} ?>Combat : <span id="cds"><?php echo get_colored_text_range($ThisShip->getCDs(),$ThisShip->getMaxCDs(),number_format($ThisShip->getCDs()).'/'.number_format($ThisShip->getMaxCDs())); ?></span><br /><?php
	
	if(isset($DropSDLink)) { ?>
		<a href="<?php echo $DropSDLink; ?>">
			<span class="bold">[X]</span>
		</a><?php
	} ?>Scout : <?php echo $ThisShip->getSDs(); ?>/<?php echo $ThisShip->getMaxSDs(); ?><br />
	<br />
	<a href="<?php echo $CargoJettisonLink; ?>"><span class="bold">Cargo Holds (<?php echo $ThisShip->getCargoHolds(); ?>/<?php echo $ThisShip->getMaxCargoHolds(); ?>)</span></a><br />
	<?php
	if($ThisShip->hasCargo()) {
		foreach($ThisShip->getCargo() as $GoodID => $GoodAmount) {
			$Good = Globals::getGood($GoodID); ?>
			<img src="<?php echo $Good['ImageLink']; ?>" width="13" height="16" title="<?php echo $Good['Name']; ?>" alt="<?php echo $Good['Name']; ?>"> : <?php echo $GoodAmount; ?><br /><?php
		}
	} ?>
	Empty : <?php echo $ThisShip->getEmptyHolds(); ?><br /><br />
	<a href="<?php echo $WeaponReorderLink; ?>"><span class="bold">Weapons</span></a>&nbsp;<a href="weapon_list.php" target="_blank"><img src="images/silk/help.png" width="16" height="16" alt="Weapon List" title="Weapon List"/></a><br /><?php
	if($ThisShip->hasWeapons()) { ?>
		<div class="wep_drop1" id="hide-show" onclick="toggleWepD('<?php echo $ThisPlayer->getToggleWeaponHidingHREF(); ?>');">
			<a href="<?php echo $ThisPlayer->getToggleWeaponHidingHREF(); ?>" onclick="this.href='javascript:void(0)';">
				Show/Hide (<?php echo $ThisShip->getNumWeapons(); ?>)<br />
			</a>
			<span class="wep1"<?php if(!$ThisPlayer->isDisplayWeapons()){ ?> style="display: none;"<?php } ?>><?php
				foreach ($ThisShip->getWeapons() as $Weapon) {
					echo $Weapon->getName(); ?><br /><?php
				} ?>
			</span>
		</div><?php
	} ?>
	Open : <?php echo $ThisShip->getOpenWeaponSlots(); ?><br />
	Total Damage: (<?php echo $ThisShip->getTotalShieldDamage(); ?>/<?php echo $ThisShip->getTotalArmourDamage(); ?>)<br />
	Power Used: <?php echo $ThisShip->getPowerUsed(); ?>/<?php echo $ThisShip->getMaxPower(); ?><br /><br /><?php
	if(ENABLE_BETA) {
		?><a href="<?php echo Globals::getBetaFunctionsHREF(); ?>"><span class="bold">Beta Functions</span></a><?php
	}
} ?>
