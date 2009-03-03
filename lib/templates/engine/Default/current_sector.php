<h1><?php echo $Topic ?></h1><br />
<div class="bar1">
	<div>
		<span class="nowrap"><a href="<?php echo $PlotCourseLink ?>">Plot a Course</a></span> | <span class="nowrap"><a href="<?php echo $LocalMapLink ?>">Local Map</a></span> | <span class="nowrap"><a href="map_galaxy.php" target="_blank">Galaxy Map</a></span>
	</div>
</div><br />
<table cellspacing="0" cellpadding="0" style="width:100%;border:none">
	<tr>
		<td style="padding:0px;vertical-align:top">
			<?php $this->includeTemplate('includes/SectorNavigation.inc'); ?>
		</td>
		<td style="padding:0px;vertical-align:top;width:32em;"><?php
		$this->includeTemplate('includes/PlottedCourse.inc');
		$this->includeTemplate('includes/Ticker.inc');
		if($ErrorMessage)
		{
			echo $ErrorMessage ?><br /><?php
		}
		if($ProtectionMessage)
		{
			echo $ProtectionMessage ?><br /><?php
		}
		if($TurnsMessage)
		{
			echo $TurnsMessage ?><br /><?php
		}
		if($TradeMessage)
		{
			echo $TradeMessage ?><br /><?php
		}
		if($ForceRefreshMessage)
		{
			echo $ForceRefreshMessage ?><br /><?php
		}
		if($VarMessage)
		{
			echo $VarMessage ?><br /><?php
		} ?>
		</td>
	</tr>
</table><br /><?php
$this->includeTemplate('includes/SectorPlanet.inc');
$this->includeTemplate('includes/SectorPort.inc');
$this->includeTemplate('includes/SectorLocations.inc');
$this->includeTemplate('includes/SectorPlayers.inc');
$this->includeTemplate('includes/SectorForces.inc'); ?>
<br />