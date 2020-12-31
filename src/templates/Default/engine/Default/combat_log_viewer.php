<?php
if (isset($PreviousLogHREF) || isset($NextLogHREF)) { ?>
	<div class="center"><?php
	if (isset($PreviousLogHREF)) {
		?><a href="<?php echo $PreviousLogHREF ?>"><img title="Previous" alt="Previous" src="images/album/rew.jpg" /></a><?php
	}
	if (isset($NextLogHREF)) {
		?><a href="<?php echo $NextLogHREF ?>"><img title="Next" alt="Next" src="images/album/fwd.jpg" /></a><?php
	} ?>
	</div><?php
} ?>
Sector <?php echo $CombatLogSector ?><br />
<?php echo $CombatLogTimestamp ?><br />
<br />

<?php
if ($CombatResultsType == 'PLAYER') {
	$this->includeTemplate('includes/TraderFullCombatResults.inc.php', array('TraderCombatResults'=>$CombatResults));
} elseif ($CombatResultsType == 'FORCE') {
	$this->includeTemplate('includes/ForceFullCombatResults.inc.php', array('FullForceCombatResults'=>$CombatResults));
} elseif ($CombatResultsType == 'PORT') {
	$this->includeTemplate('includes/PortFullCombatResults.inc.php', array('FullPortCombatResults'=>$CombatResults,
	                                                                  'MinimalDisplay'=>false,
	                                                                  'AlreadyDestroyed'=>false));
} elseif ($CombatResultsType == 'PLANET') {
	$this->includeTemplate('includes/PlanetFullCombatResults.inc.php', array('FullPlanetCombatResults'=>$CombatResults,
	                                                                    'MinimalDisplay'=>false,
	                                                                    'AlreadyDestroyed'=>false));
}
?>
