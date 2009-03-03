<?php if($ThisPlayer->hasPlottedCourse())
{
	$PlottedCourse = $ThisPlayer->getPlottedCourse();
	$NextSector =& $ThisSector->getSector($ThisPlayer->getGameID(),$PlottedCourse['NextSector'],$ThisPlayer->getAccountID()); ?>
	<table class="nobord" width="100%">
		<tr>
			<td<?php if($ThisShip->hasScanner()){ ?> rowspan="2"<?php }?>><?php echo $PlottedCourse['CourseString'] ?><br />
				(<?php echo $PlottedCourse['Length'] ?> sectors)
			</td>
			<td class="top right">
				<div class="buttonA">
					<a class="buttonA" href="<?php echo $NextSector->getCurrentSectorHREF() ?>">&nbsp; Follow Course (<?php echo $PlottedCourse['NextSector'] ?>) &nbsp;</a>
				</div>
			</td>
		</tr>
		<?php
		if($ThisShip->hasScanner())
		{ ?>
			<tr>
				<td class="top right">
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $NextSector->getScanSectorHREF() ?>">&nbsp; Scan Course (<?php echo $PlottedCourse['NextSector'] ?>) &nbsp;</a>
					</div>
				</td>
			</tr>
		<?php } ?>
	</table>
<?php
}
?>