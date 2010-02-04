<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $Title; ?> <?php if(isset($GameName)) echo $GameName; ?></title>
		<meta http-equiv="pragma" content="no-cache"><?php
		if(is_object($ThisAccount) && $ThisAccount->isDefaultCSSEnabled())
		{
			?><link rel="stylesheet" type="text/css" href="<?php echo URL; ?>/css/htcCSS2.css"><?php // echo $CSSLink;
		}
		if(isset($ExtraCSSLink))
		{
			?><link rel="stylesheet" type="text/css" href="<?php echo $ExtraCSSLink; ?>"><?php
		} ?>
		<style type="text/css">
			body {
				font-size:<?php echo $FontSize; ?>%;
			}
		</style><?php
		if(isset($HeaderTemplateInclude))
		{
			$this->includeTemplate($HeaderTemplateInclude);
		} ?>
	</head>
	<body>
		<div id="Container">
			<table class="tableHeight">
				<tr>
					<td class="topleftCell">
						<b><span style="font-size: 72px;">smr</span></b>
						<br />
						<span id="tod"><?php echo $timeDisplay; ?></span>
					</td>
					<td class="topcenterCell">
						<div class="TopInfor"><?php
							if(isset($ThisPlayer))
							{ ?>
								<div class="name">
									<?php echo $ThisPlayer->getLevelName(); ?>
									<br />
									<a class="Nav" href="<?php echo $PlayerNameLink; ?>"><?php echo $ThisPlayer->getDisplayName(); ?></a>
								</div>
		
								<div class="topcenterOne">
									Race: <?php echo $ThisPlayer->getRaceName(); ?><br />
									
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
											?>Newbie Turns: <span style="color: #<?php if ($ThisPlayer->getNewbieTurns() > NEWBIE_TURNS_WARNING_LIMIT) { ?>387C44<?php } else { ?>F00<?php } ?>;"><?php echo $ThisPlayer->getNewbieTurns(); ?></span><br /><?php
										} ?>
									</span>
									
									Credits: <?php echo number_format($ThisPlayer->getCredits()); ?><br />
									
									Experience: <?php echo number_format($ThisPlayer->getExperience()); ?>
								</div>
		
								<div class="topcenterTwo">
									Level: <a class="Nav" href="<?php echo URL; ?>/level_requirements.php" target="levelRequirements"><?php echo $ThisPlayer->getLevelID(); ?></a>
									<br />
									Next Level: <br /><?php
										$NextLevelExperience = number_format($ThisPlayer->getNextLevelExperience());
										$Experience = number_format($ThisPlayer->getExperience()); ?>
										<img src="images/bar_left.gif" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
										<img src="images/blue.gif" style="width:<?php echo $ThisPlayer->getNextLevelPercentAcquired(); ?>px;height:10px;" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
										<img src="images/bar_border.gif" style="width:<?php echo $ThisPlayer->getNextLevelPercentRemaining(); ?>px;height:10px;" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
										<img src="images/bar_right.gif" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" /><br />
									<br />
									Alignment: <?php echo get_colored_text($ThisPlayer->getAlignment(),number_format($ThisPlayer->getAlignment())); ?>
									<br />
									Alliance: <a href="<?php echo $AllianceLink; ?>"><?php
										echo $ThisPlayer->getAllianceName();
										if($ThisPlayer->hasAlliance())
										{
											echo '('.number_format($ThisPlayer->getAllianceID()).')';
										} ?></a>
								</div>
								<div style="clear:left;"/>
								<!--div style="border-top: 2px solid #a3692d; margin-top: 5px;"></div>
								<div class="bar1">
									<a class="Nav" href="htcRound.htm">Plot A Cource</a> | 
									<a class="Nav" href="htcRound.htm">Local Map</a> | 
									<a class="Nav" href="htcRound.htm">Galaxy Map</a>
								</div--><?php
							} ?>
						</div>
					</td>
					<td rowspan="2" class="rightCell">
						<div class="RightInfor"><?php
							if(isset($ThisPlayer))
							{ ?>
								<div class="messages">
								<span id="message_area"><?php
									if(isset($MessageGlobalLink))
									{ ?>
										<a class="message" title="Global Messages" href="<?php echo $MessageGlobalLink; ?>"><img src="images/global_msg.png" alt="Global Messages"/></a>
										<span class="small"><?php echo $MessageGlobalNum; ?></span><?php
									}
									if(isset($MessagePersonalLink))
									{ ?>
										<a class="message" title="Personal Messages" href="<?php echo $MessagePersonalLink; ?>"><img src="images/personal_msg.png" alt="Personal Messages"/></a>
										<span class="small"><?php echo $MessagePersonalNum; ?></span><?php
									}
									if(isset($MessageScoutLink))
									{ ?>
										<a class="message" title="Scout Messages" href="<?php echo $MessageScoutLink; ?>"><img src="images/scout_msg.png" alt="Scout Messages"/></a>
										<span class="small"><?php echo $MessageScoutNum; ?></span><?php
									}
									if(isset($MessagePoliticalLink))
									{ ?>
										<a class="message" title="Political Messages" href="<?php echo $MessagePoliticalLink; ?>"><img src="images/council_msg.png" alt="Political Messages"/></a>
										<span class="small"><?php echo $MessagePoliticalNum; ?></span><?php
									}
									if(isset($MessageAllianceLink))
									{ ?>
										<a class="message" title="Alliance Messages" href="<?php echo $MessageAllianceLink; ?>"><img src="images/alliance_msg.png" alt="Alliance Messages"/></a>
										<span class="small"><?php echo $MessageAllianceNum; ?></span><?php
									}
									if(isset($MessageAdminLink))
									{ ?>
										<a class="message" title="Admin Messages" href="<?php echo $MessageAdminLink; ?>"><img src="images/admin_msg.png" alt="Admin Messages"/></a>
										<span class="small"><?php echo $MessageAdminNum; ?></span><?php
									}
									if(isset($MessagePlanetLink))
									{ ?>
										<a class="message" title="Planet Messages" href="<?php echo $MessagePlanetLink ?>"><img src="images/planet_msg.png" alt="Planet Messages"/></a>
										<span class="small"><?php echo $MessagePlanetNum; ?></span><?php
									} 
									if(isset($MessageGlobalLink) || isset($MessagePersonalLink) || isset($MessageScoutLink) || isset($MessagePoliticalLink) || isset($MessageAllianceLink) || isset($MessageAdminLink) || isset($MessagePlanetLink))
									{
										?><br /><?php
									} ?>
									</span>
								</div>
							
								<div class="RightInforShip">
								<a href="<?php echo URL; ?>/ship_list.php" target="shipList"><span id="ship_name"><?php echo $ThisShip->getName(); ?></span></a><br /><?php
								if($ThisPlayer->hasCustomShipName())
								{
									echo $ThisPlayer->getCustomShipName(); ?><br /><?php
								} ?>
								Rating: <span id="offense"><?php echo number_format($ThisShip->getAttackRating()); ?></span>/<span id="defense"><?php echo number_format($ThisShip->getDefenseRating()); ?></span><br />
		
								Shields: <span id="shields"><?php echo $ThisShip->getShields(); ?>/<?php echo $ThisShip->getMaxShields(); ?></span><br />
		
								Armor: <span id="armour"><?php echo $ThisShip->getArmour(); ?>/<?php echo $ThisShip->getMaxArmour(); ?></span><br />
								<br />
								<a class="Nav" href="<?php echo $HardwareLink; ?>">CIJSD: <?php
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
								?></a><br /><?php
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
								<br /><br />
								<a class="Nav big" href="<?php echo $ForceDropLink; ?>">Forces</a><br /><?php
								
								if(isset($DropMineLink))
								{ ?>
									<a class="Nav" href="<?php echo $DropMineLink; ?>">
										<span class="bold">[X]</span>
									</a><?php
								} ?>Mines : <?php echo $ThisShip->getMines(); ?>/<?php echo $ThisShip->getMaxMines(); ?><br /><?php
			
								if(isset($DropCDLink))
								{ ?>
									<a class="Nav" href="<?php echo $DropCDLink; ?>">
										<span class="bold">[X]</span>
									</a><?php
								} ?>Combat : <?php echo $ThisShip->getCDs(); ?>/<?php echo $ThisShip->getMaxCDs(); ?><br /><?php
			
								if(isset($DropSDLink))
								{ ?>
									<a class="Nav" href="<?php echo $DropSDLink; ?>">
										<span class="bold">[X]</span>
									</a><?php
								} ?>Scout : <?php echo $ThisShip->getSDs(); ?>/<?php echo $ThisShip->getMaxSDs(); ?><br />
								<br />
								
								<a class="Nav" href="<?php echo $CargoJettisonLink; ?>">Cargo Holds (<?php echo $ThisShip->getCargoHolds(); ?>/<?php echo $ThisShip->getMaxCargoHolds(); ?>)</a><br /><?php
								
								if($ThisShip->hasCargo())
								{
									foreach($ThisShip->getCargo() as $GoodID => $GoodAmount)
									{
										$Good =& Globals::getGood($GoodID); ?>
										<img src="<?php echo $Good['ImageLink']; ?>" alt="<?php echo $Good['Name']; ?>"> : <?php echo $GoodAmount; ?><br /><?php
									}
								} ?>
								
								Empty : <?php echo $ThisShip->getEmptyHolds(); ?><br />
								<br />
								<a class="Nav big" href="<?php echo $WeaponReorderLink; ?>">Weapons</a><br /><?php
								if($ThisShip->hasWeapons())
								{ ?>
									<div class="wep_drop1" id="hide-show" onclick="toggleWepD(<?php echo $ThisShip->getNumWeapons(); ?>,'<?php echo $ThisPlayer->getToggleWeaponHidingHREF(); ?>');">
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
								Open : <?php echo $ThisShip->getOpenWeaponSlots(); ?><br />
								<span class="noWrap">Total Damage: (<?php echo $ThisShip->getTotalShieldDamage(); ?>/<?php echo $ThisShip->getTotalArmourDamage(); ?>)</span><br />
								Power Used: <?php echo $ThisShip->getPowerUsed(); ?>/<?php echo $ThisShip->getMaxPower();
								if(ENABLE_BETA)
								{
									?><br /><br /><a class="Nav" href="<?php echo Globals::getBetaFunctionsHREF(); ?>">Beta Functions</a><?php
								} ?>
							</div><?php
							} ?>
						</div>
					</td>
				</tr>
				<tr>
	
					<td class="leftCell">
						<?php $this->includeTemplate('includes/LeftPanel.inc'); ?>
					</td>
					
					<td class="centerContent">
						<div id="middle_panel" class="MainContentArea"><?php
							if(isset($PageTopic))
							{
								?><h1><?php echo $PageTopic; ?></h1><br /><?php
							}
							if(isset($MenuBar))
							{
								echo $MenuBar;
							}
							$this->includeTemplate($TemplateBody); ?>
						</div>
						<div class="copyright">
							SMR <?php echo $Version; ?>&copy;2007-<?php echo $CurrentYear; ?> Page and SMR
							<br />
							Kindly Hosted by FeM
							<br />
							Script runtime: <span id="rt"><?php echo $ScriptRuntime; ?></span> seconds
							<br />
							<a href="imprint.html">[Imprint]</a>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<script type="text/javascript" src="js/ajax.js"></script><?php
		if(!empty($js))
		{
			?><script type="text/javascript" src="<?php echo $js; ?>"></script><?php
		} 
		if($AJAX_ENABLE_REFRESH)
		{
			?><script type="text/javascript">window.onload=function(){startRefresh('<?php echo $AJAX_ENABLE_REFRESH; ?>');}</script><?php
		} ?>
	</body>
</html>