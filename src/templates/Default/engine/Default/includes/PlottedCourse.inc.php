<?php declare(strict_types=1);

use Smr\Pages\Player\PlotCourseCancelProcessor;
use Smr\Pages\Player\PlotCourseConventionalProcessor;
use Smr\Sector;

/**
 * @var Smr\Player $ThisPlayer
 * @var Smr\Sector $ThisSector
 * @var Smr\Ship $ThisShip
 */

if ($ThisPlayer->hasPlottedCourse()) {
	$PlottedCourse = $ThisPlayer->getPlottedCourse();
	$CancelCourseHREF = (new PlotCourseCancelProcessor())->href();
	$ReplotCourseHREF = (new PlotCourseConventionalProcessor(to: $PlottedCourse->getEndSectorID(), from: $ThisSector->getSectorID()))->href();
	$NextSector = Sector::getSector($ThisPlayer->getGameID(), $PlottedCourse->getNextOnPath()); ?>
	<table class="nobord fullwidth">
		<tr>
			<td class="top left">
				<h2>Plotted Course</h2><br />
				<?php echo implode(' - ', $PlottedCourse->getPath()); ?><br />
				(<?php echo pluralise($PlottedCourse->getLength(), 'sector'); ?>,
				<?php echo pluralise($PlottedCourse->getTurns(), 'turn'); ?>)
			</td>
			<td class="top right"><?php
				if ($ThisSector->isLinked($NextSector->getSectorID())) { ?>
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $NextSector->getCurrentSectorMoveHREF($ThisPlayer); ?>">Follow Course (#<?php echo $PlottedCourse->getNextOnPath(); ?>)</a>
					</div><?php
					if ($ThisShip->hasScanner()) { ?>
						<br /><br />
						<div class="buttonA">
							<a class="buttonA" href="<?php echo $NextSector->getSectorScanHREF($ThisPlayer); ?>">Scan Course (#<?php echo $PlottedCourse->getNextOnPath(); ?>)</a>
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
