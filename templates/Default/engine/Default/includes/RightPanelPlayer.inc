<?php
if (isset($GameID)) { ?>
	<span id="attack_area"><?php if ($UnderAttack) { ?><p class="attack_warning">You Are Under Attack!</p><script>triggerAttackBlink('3B1111');</script><?php } ?></span><?php
	$this->includeTemplate('includes/UnreadMessages.inc'); ?>
	<a href="level_requirements.php" target="levelRequirements"><span id="lvlName"><?php echo $ThisPlayer->getLevelName(); ?></span></a><br />
	<a class="big" href="<?php echo $PlayerNameLink; ?>"><?php echo $ThisPlayer->getDisplayName(); ?></a><br /><?php
	if (isset($PlayerInvisible)) { ?>
		<span class="smallFont smallCaps red">INVISIBLE</span><br /><?php
	} ?>
	<br />
	Race : <a href="<?php echo Globals::getCouncilHREF($ThisPlayer->getRaceID()); ?>"><?php echo $ThisPlayer->getColouredRaceName($ThisPlayer->getRaceID()); ?></a><br />
	Turns : <span id="turns">
		<span class="<?php echo $ThisPlayer->getTurnsColor(); ?>"><?php
				echo $ThisPlayer->getTurns() . '/' . $ThisPlayer->getMaxTurns();
			?></span>
		</span><br />
	<span id="newbieturns"><?php
		if ($ThisPlayer->hasNewbieTurns()) {
			?>Newbie Turns : <span class="<?php
			if ($ThisPlayer->getNewbieTurns() > NEWBIE_TURNS_WARNING_LIMIT) { ?>green<?php } else { ?>red<?php } ?>"><?php echo $ThisPlayer->getNewbieTurns(); ?></span><br /><?php
		} ?>
	</span>
	Credits : <span id="creds"><?php echo number_format($ThisPlayer->getCredits()); ?></span><br />
	Experience : <span id="exp"><?php echo number_format($ThisPlayer->getExperience()); ?></span><br />
	<a href="level_requirements.php" target="levelRequirements">Level : <span id="lvl"><?php echo $ThisPlayer->getLevelID(); ?></span></a><br />
	<a href="level_requirements.php" target="levelRequirements">Next Level: </a><a href="<?php echo WIKI_URL; ?>/game-guide/experience-levels" target="_blank"><img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Experience Levels"/></a><br /><?php
	$NextLevelExperience = number_format($ThisPlayer->getNextLevelExperience());
	$Experience = number_format($ThisPlayer->getExperience()); ?>
	<a href="level_requirements.php" target="levelRequirements">
		<span id="lvlBar">
			<img src="images/bar_left.gif" width="5" height="10" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
			<img src="images/blue.gif" width="<?php echo $ThisPlayer->getNextLevelPercentAcquired(); ?>" height="10" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
			<img src="images/bar_border.gif" width="<?php echo $ThisPlayer->getNextLevelPercentRemaining(); ?>" height="10" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" />
			<img src="images/bar_right.gif" width="5" height="10" title="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" alt="<?php echo $Experience; ?>/<?php echo $NextLevelExperience; ?>" /><br />
		</span>
	</a>
	Alignment : <span id="align"><?php echo get_colored_text($ThisPlayer->getAlignment(), number_format($ThisPlayer->getAlignment())); ?></span><br />
	Alliance : <span id="alliance"><a href="<?php echo Globals::getAllianceHREF($ThisPlayer->getAllianceID()); ?>"><?php echo $ThisPlayer->getAllianceName(false, true); ?></a></span><br />
	<br />
	<?php
}
?>
