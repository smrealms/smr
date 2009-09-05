<?php $this->includeTemplate('includes/menu.inc',array('MenuItems' => array(
					array('Link'=>$PlotCourseLink,'Text'=>'Plot a Course'),
					array('Link'=>$LocalMapLink,'Text'=>'Local Map'),
					array('Link'=>'map_galaxy.php" target="_blank','Text'=>'Galaxy Map')))); ?>
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
				if(isset($AttackResults))
				{
					if($AttackResultsType=='PLAYER')
					{
						$this->includeTemplate('includes/TraderFullCombatResults.inc',array('TraderCombatResults'=>$AttackResults,'MinimalDisplay'=>true));
					}
					else if($AttackResultsType=='FORCE')
					{
						$this->includeTemplate('includes/ForceFullCombatResults.inc',array('FullForceCombatResults'=>$AttackResults,'MinimalDisplay'=>true));
					}
					else if($AttackResultsType=='PORT')
					{
						$this->includeTemplate('includes/PortFullCombatResults.inc',array('FullPortCombatResults'=>$AttackResults,'MinimalDisplay'=>true));
					}
					else if($AttackResultsType=='PLANET')
					{
						$this->includeTemplate('includes/PlanetFullCombatResults.inc',array('FullPlanetCombatResults'=>$AttackResults,'MinimalDisplay'=>true));
					}
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