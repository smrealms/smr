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
			$this->includeTemplate('includes/Ticker.inc'); ?>
			<span id="secmess"><?php
				if(isset($ErrorMessage))
				{
					echo $ErrorMessage ?><br /><?php
				}
				if(isset($ProtectionMessage))
				{
					echo $ProtectionMessage ?><br /><?php
				}
				if(isset($TurnsMessage))
				{
					echo $TurnsMessage ?><br /><?php
				}
				if(isset($TradeMessage))
				{
					echo $TradeMessage ?><br /><?php
				}
				if(isset($ForceRefreshMessage))
				{
					echo $ForceRefreshMessage ?><br /><?php
				}
				if(isset($VarMessage))
				{
					echo $VarMessage ?><br /><?php
				} ?>
			</span>
		</td>
	</tr>
</table><br /><?php
$this->includeTemplate('includes/SectorPlanet.inc');
$this->includeTemplate('includes/SectorPort.inc');
$this->includeTemplate('includes/SectorLocations.inc');
$this->includeTemplate('includes/SectorPlayers.inc');
$this->includeTemplate('includes/SectorForces.inc'); ?>
<br />