<table class="fullwidth" style="border:none">
	<tr>
		<td class="top nopad"><?php
			$this->includeTemplate('includes/SectorNavigation.inc'); ?>
		</td>
		<td class="top nopad" style="width:32em;"><?php
			$this->includeTemplate('includes/PlottedCourse.inc');
			$this->includeTemplate('includes/Ticker.inc');
			$this->includeTemplate('includes/Missions.inc'); ?>
			<span id="secmess"><?php
				if(isset($ErrorMessage)) {
					echo $ErrorMessage; ?><br /><?php
				}
				if(isset($ProtectionMessage)) {
					echo $ProtectionMessage; ?><br /><?php
				}
				if(isset($TurnsMessage)) {
					echo $TurnsMessage; ?><br /><?php
				}
				if(isset($TradeMessage)) {
					echo $TradeMessage; ?><br /><?php
				}
				if(isset($ForceRefreshMessage)) {
					echo $ForceRefreshMessage; ?><br /><?php
				}
				if(isset($AttackResults)) {
					if($AttackResultsType=='PLAYER') {
						$this->includeTemplate('includes/TraderFullCombatResults.inc',array('TraderCombatResults'=>$AttackResults,'MinimalDisplay'=>true));
					}
					else if($AttackResultsType=='FORCE') {
						$this->includeTemplate('includes/ForceFullCombatResults.inc',array('FullForceCombatResults'=>$AttackResults,'MinimalDisplay'=>true));
					}
					else if($AttackResultsType=='PORT') {
						$this->includeTemplate('includes/PortFullCombatResults.inc',array('FullPortCombatResults'=>$AttackResults,'MinimalDisplay'=>true,'AlreadyDestroyed'=>false));
					}
					else if($AttackResultsType=='PLANET') {
						$this->includeTemplate('includes/PlanetFullCombatResults.inc',array('FullPlanetCombatResults'=>$AttackResults,'MinimalDisplay'=>true,'AlreadyDestroyed'=>false));
					} ?><br /><?php
				}
				if(isset($VarMessage)) {
					echo $VarMessage; ?><br /><?php
				} ?>
			</span>
		</td>
	</tr>
</table><br /><?php
$this->includeTemplate('includes/SectorPlanet.inc');
$this->includeTemplate('includes/SectorPort.inc');
$this->includeTemplate('includes/SectorLocations.inc');
$this->includeTemplate('includes/SectorPlayers.inc',array('PlayersContainer'=>$ThisSector));
$this->includeTemplate('includes/SectorForces.inc'); ?>
<br />
