<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head><?php
		$this->includeTemplate('includes/Head.inc'); ?>
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
						if (isset($ThisPlayer)) { ?>
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
												Race: <a href="<?php echo Globals::getCouncilHREF($ThisPlayer->getRaceID()); ?>"><?php echo $ThisPlayer->getColouredRaceName($ThisPlayer->getRaceID()); ?></a><br />
												
												Turns : <span id="turns">
													<span class="<?php echo $ThisPlayer->getTurnsColor(); ?>"><?php
															echo $ThisPlayer->getTurns() . '/' . $ThisPlayer->getMaxTurns();
														?></span>
													</span><br />
												
												<span id="newbieturns"><?php
													if ($ThisPlayer->hasNewbieTurns()) {
														?>Newbie Turns: <span style="color: #<?php if ($ThisPlayer->getNewbieTurns() > NEWBIE_TURNS_WARNING_LIMIT) { ?>387C44<?php } else { ?>F00<?php } ?>;"><?php echo $ThisPlayer->getNewbieTurns(); ?></span><br /><?php
													} ?>
												</span>
												
												Credits: <span id="creds"><?php echo number_format($ThisPlayer->getCredits()); ?></span><br />
												
												Experience: <span id="exp"><?php echo number_format($ThisPlayer->getExperience()); ?></span>
											</div>
										</td>
										<td>
											<div class="topcenterTwo noWrap">
												Level: <a class="nav" href="/level_requirements.php" target="levelRequirements"><span id="lvl"><?php echo $ThisPlayer->getLevelID(); ?></span></a>
												<br />
												Next Level: <?php
													$NextLevelExperience = number_format($ThisPlayer->getNextLevelExperience());
													$Experience = number_format($ThisPlayer->getExperience()); ?>
													<span id="lvlBar">
														<img src="images/bar_left.gif" width="5" height="10" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
														<img src="images/blue.gif" width="<?php echo $ThisPlayer->getNextLevelPercentAcquired(); ?>" height="10" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
														<img src="images/bar_border.gif" width="<?php echo $ThisPlayer->getNextLevelPercentRemaining(); ?>" height="10" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
														<img src="images/bar_right.gif" width="5" height="10" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" /><br />
													</span>
												
												Alignment: <span id="align"><?php echo get_colored_text($ThisPlayer->getAlignment(), number_format($ThisPlayer->getAlignment())); ?></span><br />
												
												Alliance: <span id="alliance"><a href="<?php echo Globals::getAllianceHREF($ThisPlayer->getAllianceID()); ?>"><?php
													echo $ThisPlayer->getAllianceName(false, true); ?></a></span>
											</div>
										</td>
									</tr>
								</table>
								<div class="clear"></div><?php
								if (isset($MenuItems) || isset($MenuBar)) { ?>
									<div class="bar1Separator"></div>
									<div class="bar1"><?php
										if (isset($MenuItems)) {
											$this->includeTemplate('includes/menu.inc');
										} else if (isset($MenuBar)) {
											echo $MenuBar;
										} ?>
									</div><?php
								} else if (isset($SubMenuBar)) {
									echo $SubMenuBar;
								} ?>
							</div><?php
						} ?>
					</td>
					<td rowspan="2" class="rightCell"><?php
						if (isset($ThisPlayer)) { ?>
							<div class="rightInfo noWrap">
								<div class="messages"><?php
									$this->includeTemplate('includes/UnreadMessages.inc'); ?>
								</div>

								<div class="rightInfoShip">
									<?php $this->includeTemplate('includes/RightPanelShip.inc'); ?>
								</div>
							</div><?php
						} ?>
					</td>
				</tr>
				<tr>
					<td class="leftCell">
						<?php $this->includeTemplate('includes/LeftPanel.inc'); ?>
					</td>
					
					<td class="centerContent">
						<div id="middle_panel" class="MainContentArea<?php if (isset($SpaceView) && $SpaceView) { ?> stars<?php } ?>"><?php
							if (isset($PageTopic)) {
								?><h1><?php echo $PageTopic; ?></h1><br /><?php
							}
							$this->includeTemplate($TemplateBody); ?>
						</div>
						<div class="footer_left">
							<?php $this->includeTemplate('includes/VoteLinks.inc'); ?>
						</div>
						<div class="footer_right">
							<?php $this->includeTemplate('includes/copyright.inc'); ?>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<?php $this->includeTemplate('includes/EndingJavascript.inc'); ?>
	</body>
</html>
