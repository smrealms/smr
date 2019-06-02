<?php
if ($ThisPlayer->hasPlottedCourse()) {
	$PlottedCourse = $ThisPlayer->getPlottedCourse();
	$CancelCourseHREF = SmrSession::getNewHREF(create_container('course_plot_cancel_processing.php'));
	$ReplotCourseHREF = SmrSession::getNewHREF(create_container('course_plot_processing.php', '', array('to' => $PlottedCourse->getEndSectorID(), 'from' => $ThisSector->getSectorID())));
	$NextSector = SmrSector::getSector($ThisPlayer->getGameID(), $PlottedCourse->getNextOnPath(), $ThisPlayer->getAccountID()); ?>
	<table class="nobord fullwidth">
		<tr>
			<td class="top left">
				<h2>Plotted Course</h2><br />
				<?php echo implode(' - ', $PlottedCourse->getPath()); ?><br />
				(<?php $s = $PlottedCourse->getTotalSectors(); echo $s . ' ' . pluralise('sector', $s); ?>,
				<?php $t = $PlottedCourse->getTurns(); echo $t . ' ' . pluralise('turn', $t); ?>)
			</td>
			<td class="top right"><?php
				if ($ThisSector->isLinked($NextSector->getSectorID())) { ?>
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $NextSector->getCurrentSectorMoveHREF(); ?>">Follow Course (#<?php echo $PlottedCourse->getNextOnPath(); ?>)</a>
					</div><?php
					if ($ThisShip->hasScanner()) { ?>
						<br /><br />
						<div class="buttonA">
							<a class="buttonA" href="<?php echo $NextSector->getScanSectorHREF(); ?>">Scan Course (#<?php echo $PlottedCourse->getNextOnPath(); ?>)</a>
						</div><?php
					}
				} else { ?>
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $ReplotCourseHREF; ?>">Replot Course to #<?php echo $PlottedCourse->getEndSectorID(); ?></a>
					</div><?php
				} ?>
				<br /><br />
				<div class="buttonA">
					<a class="buttonA" href="<?php echo $CancelCourseHREF; ?>">Cancel Course</a>
				</div>
			</td>
		</tr>
	</table><?php
}
?>
