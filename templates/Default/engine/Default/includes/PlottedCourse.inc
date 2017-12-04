<?php
if($ThisPlayer->hasPlottedCourse()) {
	$PlottedCourse =& $ThisPlayer->getPlottedCourse();
	$NextSector =& SmrSector::getSector($ThisPlayer->getGameID(),$PlottedCourse->getNextOnPath(),$ThisPlayer->getAccountID()); ?>
	<table class="nobord fullwidth">
		<tr>
			<td class="top left">
				<h2>Plotted Course</h2><br />
				<?php echo implode(' - ',$PlottedCourse->getPath()); ?><br />
				(<?php echo $PlottedCourse->getTotalSectors() ?> sectors, <?php echo $PlottedCourse->getTurns(); ?> turns)
			</td>
			<td class="top right">
				<div class="buttonA">
					<a class="buttonA" href="<?php echo $NextSector->getCurrentSectorHREF(); ?>">&nbsp; Follow Course (<?php echo $PlottedCourse->getNextOnPath(); ?>) &nbsp;</a>
				</div><?php
				if($ThisShip->hasScanner()) { ?>
					<br /><br />
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $NextSector->getScanSectorHREF(); ?>">&nbsp; Scan Course (<?php echo $PlottedCourse->getNextOnPath(); ?>) &nbsp;</a>
					</div><?php
				} ?>
			</td>
		</tr>
	</table><?php
}
?>
