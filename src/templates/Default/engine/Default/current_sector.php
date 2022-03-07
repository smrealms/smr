<table class="fullwidth" style="border:none">
	<tr>
		<td class="top nopad"><?php
			$this->includeTemplate('includes/SectorNavigation.inc.php'); ?>
		</td>
		<td class="top nopad" style="width:32em;"><?php
			$this->includeTemplate('includes/PlottedCourse.inc.php');
			$this->includeTemplate('includes/Ticker.inc.php');
			$this->includeTemplate('includes/Missions.inc.php'); ?>
			<span id="secmess"><?php
				if (isset($ErrorMessage)) {
					echo $ErrorMessage; ?><br /><?php
				}
				if (isset($ProtectionMessage)) {
					echo $ProtectionMessage; ?><br /><?php
				}
				if (isset($TurnsMessage)) {
					echo $TurnsMessage; ?><br /><?php
				}
				if (isset($TradeMessage)) {
					echo $TradeMessage; ?><br /><?php
				}
				if (isset($ForceRefreshMessage)) {
					echo $ForceRefreshMessage; ?><br /><?php
				}
				if (isset($AttackResults)) {
					if ($AttackResultsType == 'PLAYER') {
						$this->includeTemplate('includes/TraderFullCombatResults.inc.php', ['TraderCombatResults' => $AttackResults, 'MinimalDisplay' => true]);
					} elseif ($AttackResultsType == 'FORCE') {
						$this->includeTemplate('includes/ForceFullCombatResults.inc.php', ['FullForceCombatResults' => $AttackResults, 'MinimalDisplay' => true]);
					} elseif ($AttackResultsType == 'PORT') {
						$this->includeTemplate('includes/PortFullCombatResults.inc.php', ['FullPortCombatResults' => $AttackResults, 'MinimalDisplay' => true, 'AlreadyDestroyed' => false]);
					} elseif ($AttackResultsType == 'PLANET') {
						$this->includeTemplate('includes/PlanetFullCombatResults.inc.php', ['FullPlanetCombatResults' => $AttackResults, 'MinimalDisplay' => true, 'AlreadyDestroyed' => false]);
					} ?><br /><?php
				}
				if (isset($VarMessage)) {
					echo $VarMessage; ?><br /><?php
				} ?>
			</span>
		</td>
	</tr>
</table><br /><?php
$this->includeTemplate('includes/SectorPlanet.inc.php');
$this->includeTemplate('includes/SectorPort.inc.php');
$this->includeTemplate('includes/SectorLocations.inc.php');
$this->includeTemplate('includes/SectorPlayers.inc.php', ['PlayersContainer' => $ThisSector]);
$this->includeTemplate('includes/SectorForces.inc.php'); ?>
<br />
