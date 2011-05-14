<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<title><?php echo $Title; ?> <?php if(isset($GameName)) echo $GameName; ?></title>
		<meta http-equiv="pragma" content="no-cache" /><?php
		if(!is_object($ThisAccount) || $ThisAccount->isDefaultCSSEnabled())
		{ ?>
			<link rel="stylesheet" type="text/css" href="<?php echo $CSSLink; ?>" />
			<link rel="stylesheet" type="text/css" href="<?php echo $CSSColourLink; ?>" /><?php
		}
		if(isset($ExtraCSSLink))
		{
			?><link rel="stylesheet" type="text/css" href="<?php echo $ExtraCSSLink; ?>" /><?php
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
		<script type="text/javascript" src="js/smr15.js"></script>
	</head>
	<body>
		<div id="Container">
			<table class="tableHeight">
				<tr class="topRow">
					<td class="topleftCell">
						<b><span class="smrBanner">smr</span></b>
						<br />
						<span id="tod"><?php echo $timeDisplay; ?></span>
					</td>
					<td class="topcenterCell"><?php
						if(isset($ThisPlayer))
						{ ?>
							<div class="TopInfo">
								<table class="fullwidth">
									<tr>
										<td>
											<div class="name noWrap">
												<span id="lvlName"><?php echo $ThisPlayer->getLevelName(); ?></span>
												<br />
												<a class="nav" href="<?php echo $PlayerNameLink; ?>"><?php echo $ThisPlayer->getDisplayName(); ?></a>
											</div>
										</td>
										<td>
											<div class="topcenterOne noWrap">
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
												
												Credits: <span id="creds"><?php echo number_format($ThisPlayer->getCredits()); ?></span><br />
												
												Experience: <span id="exp"><?php echo number_format($ThisPlayer->getExperience()); ?></span>
											</div>
										</td>
										<td>
											<div class="topcenterTwo noWrap">
												Level: <a class="nav" href="<?php echo URL; ?>/level_requirements.php" target="levelRequirements"><span id="lvl"><?php echo $ThisPlayer->getLevelID(); ?></span></a>
												<br />
												Next Level: <?php
													$NextLevelExperience = number_format($ThisPlayer->getNextLevelExperience());
													$Experience = number_format($ThisPlayer->getExperience()); ?>
													<span id="lvlBar">
														<img src="images/bar_left.gif" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
														<img src="images/blue.gif" style="width:<?php echo $ThisPlayer->getNextLevelPercentAcquired(); ?>px;height:10px;" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
														<img src="images/bar_border.gif" style="width:<?php echo $ThisPlayer->getNextLevelPercentRemaining(); ?>px;height:10px;" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
														<img src="images/bar_right.gif" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" /><br />
													</span>
												
												Alignment: <span id="align"><?php echo get_colored_text($ThisPlayer->getAlignment(),number_format($ThisPlayer->getAlignment())); ?></span><br />
												
												Alliance: <span id="alliance"><a href="<?php echo Globals::getAllianceHREF($ThisPlayer->getAllianceID()); ?>"><?php
													echo $ThisPlayer->getAllianceName();
													if($ThisPlayer->hasAlliance())
													{
														echo '('.number_format($ThisPlayer->getAllianceID()).')';
													} ?></a></span>
											</div>
										</td>
									</tr>
								</table>
								<div class="clear"></div><?php
								if(isset($MenuItems)||isset($MenuBar))
								{ ?>
									<div class="bar1Separator"></div>
									<div class="bar1"><?php
										if(isset($MenuItems))
										{
											$this->includeTemplate('includes/menu.inc');
										}
										else if(isset($MenuBar))
										{
											echo $MenuBar;
										} ?>
									</div><?php
								}
								else if(isset($SubMenuBar))
								{
									echo $SubMenuBar;
								} ?>
							</div><?php
						} ?>
					</td>
					<td rowspan="2" class="rightCell"><?php
						if(isset($ThisPlayer))
						{ ?>
							<div class="rightInfo noWrap">
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

								<div class="rightInfoShip">
									<a href="<?php echo URL; ?>/ship_list.php" target="shipList"><span id="ship_name"><?php echo $ThisShip->getName(); ?></span></a><br /><?php
									if($ThisPlayer->hasCustomShipName())
									{
										echo $ThisPlayer->getCustomShipName(); ?><br /><?php
									} ?>
									Rating: <span id="offense"><?php echo number_format($ThisShip->getAttackRating()); ?></span>/<span id="defense"><?php echo get_colored_text_range($ThisShip->getDefenseRating(), $ThisShip->getMaxDefenseRating()); ?></span><br />
								</div><br />
								
								Shields : <span id="shields"><?php echo get_colored_text_range($ThisShip->getShields(),$ThisShip->getMaxShields(),number_format($ThisShip->getShields()).'/'.number_format($ThisShip->getMaxShields())); ?></span><br />
								
								Armour : <span id="armour"><?php echo get_colored_text_range($ThisShip->getArmour(),$ThisShip->getMaxArmour(),number_format($ThisShip->getArmour()).'/'.number_format($ThisShip->getMaxArmour())); ?></span><br />

								<a class="nav" href="<?php echo $HardwareLink; ?>">CIJSD: <?php
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
								?></a><br /><br /><?php
								if($ThisShip->hasCloak())
								{ ?>
									<a href="<?php echo $HardwareLink; ?>"><?php
										if($ThisShip->isCloaked())
										{
											?><strong class="green">*** Cloak active ***</strong><?php
										}
										else
										{
											?><strong class="red">*** Cloak inactive ***</strong><?php
										} ?>
									</a><br /><br /><?php
								}
								if($ThisShip->hasActiveIllusion())
								{ ?>
									<a href="<?php echo $HardwareLink; ?>">
										<span class="npcColor"><?php echo $ThisShip->getIllusionShipName(); ?></span><br />
										IG Rating : <?php echo $ThisShip->getIllusionAttack(); ?>/<?php echo $ThisShip->getIllusionDefense(); ?>
									</a><br />
									<br /><?php
								} ?>
								
								<a class="nav big" href="<?php echo $ForceDropLink; ?>">Forces</a><br /><?php
								
								if(isset($DropMineLink))
								{ ?>
									<a class="nav" href="<?php echo $DropMineLink; ?>">
										<span class="bold">[X]</span>
									</a><?php
								} ?>Mines : <?php echo $ThisShip->getMines(); ?>/<?php echo $ThisShip->getMaxMines(); ?><br /><?php
			
								if(isset($DropCDLink))
								{ ?>
									<a class="nav" href="<?php echo $DropCDLink; ?>">
										<span class="bold">[X]</span>
									</a><?php
								} ?>Combat : <span id="cds"><?php echo get_colored_text_range($ThisShip->getCDs(),$ThisShip->getMaxCDs()); ?>/<?php echo $ThisShip->getMaxCDs(); ?></span><br /><?php
			
								if(isset($DropSDLink))
								{ ?>
									<a class="nav" href="<?php echo $DropSDLink; ?>">
										<span class="bold">[X]</span>
									</a><?php
								} ?>Scout : <?php echo $ThisShip->getSDs(); ?>/<?php echo $ThisShip->getMaxSDs(); ?><br />
								<br />
								
								<a class="nav" href="<?php echo $CargoJettisonLink; ?>">Cargo Holds (<?php echo $ThisShip->getCargoHolds(); ?>/<?php echo $ThisShip->getMaxCargoHolds(); ?>)</a><br /><?php
								
								if($ThisShip->hasCargo())
								{
									foreach($ThisShip->getCargo() as $GoodID => $GoodAmount)
									{
										$Good =& Globals::getGood($GoodID); ?>
										<img src="<?php echo $Good['ImageLink']; ?>" alt="<?php echo $Good['Name']; ?>" /> : <?php echo $GoodAmount; ?><br /><?php
									}
								} ?>
								
								Empty : <?php echo $ThisShip->getEmptyHolds(); ?><br />
								<br />
								<a class="nav big" href="<?php echo $WeaponReorderLink; ?>">Weapons</a><br /><?php
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
									?><br /><br /><a class="nav" href="<?php echo Globals::getBetaFunctionsHREF(); ?>">Beta Functions</a><?php
								} ?>
							</div><?php
						} ?>
					</td>
				</tr>
				<tr>
					<td class="leftCell">
						<?php $this->includeTemplate('includes/LeftPanel.inc'); ?>
					</td>
					
					<td class="centerContent">
						<div id="middle_panel" class="MainContentArea<?php if(isset($SpaceView)&&$SpaceView){ ?> stars<?php } ?>"><?php
							if(isset($PageTopic))
							{
								?><h1><?php echo $PageTopic; ?></h1><br /><?php
							}
							$this->includeTemplate($TemplateBody); ?>
						</div>
						<div style="width:60%; float: left; text-align: left;">
							<span style="font-size: xx-small;">Get <b><u>FREE TURNS</u></b> for voting if you see the star, next available <span id="v"><?php if($TimeToNextVote <= 0){ ?>now<?php }else{ ?>in <?php echo format_time($TimeToNextVote,true); } ?></span>.</span>
							<br /><?php
							foreach($VoteSites as $VoteSite)
							{
								echo $VoteSite;
							} ?>
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