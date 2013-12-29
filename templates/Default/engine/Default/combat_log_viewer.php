<?php
if(isset($CombatResultsType)&&$CombatResultsType) {
	if(isset($PreviousLogHREF) || isset($NextLogHREF)) { ?>
		<div class="center"><?php
		if(isset($PreviousLogHREF)) {
			?><a href="<?php echo $PreviousLogHREF ?>"><img title="Previous" alt="Previous" src="images/album/rew.jpg" /></a><?php
		}
		if(isset($NextLogHREF)) {
			?><a href="<?php echo $NextLogHREF ?>"><img title="Next" alt="Next" src="images/album/fwd.jpg" /></a><?php
		} ?>
		</div><?php
	} ?>
	Sector <?php echo $CombatLogSector ?><br />
	<?php echo $CombatLogTimestamp ?><br />
	<br />

	<?php
	if($CombatResultsType=='PLAYER') {
		$this->includeTemplate('includes/TraderFullCombatResults.inc',array('TraderCombatResults'=>$CombatResults));
	}
	else if($CombatResultsType=='FORCE') {
		$this->includeTemplate('includes/ForceFullCombatResults.inc',array('FullForceCombatResults'=>$CombatResults));
	}
	else if($CombatResultsType=='PORT') {
		$this->includeTemplate('includes/PortFullCombatResults.inc',array('FullPortCombatResults'=>$CombatResults));
	}
	else if($CombatResultsType=='PLANET') {
		$this->includeTemplate('includes/PlanetFullCombatResults.inc',array('FullPlanetCombatResults'=>$CombatResults));
	}
}
else {
	if(isset($Message)) {?>
		<div class="center"><?php echo $Message; ?></div><br /><?php
	} ?>
	<div align="center"><?php
		$NumLogs = count($Logs);
		if($NumLogs > 0) { ?>
			There <?php echo $this->pluralise('is', $TotalLogs), ' ', $TotalLogs, $this->pluralise(' log', $NumLogs); ?> available for viewing of which <?php echo $NumLogs, ' ', $this->pluralise('is', $NumLogs); ?> being shown.<br /><br />
			<form class="standard" method="POST" action="<?php echo $LogFormHREF; ?>">
			<table class="fullwidth center">
				<tr>
					<td style="width: 30%" valign="middle"><?php
						if(isset($PreviousPage)) { ?>
							<a href="<?php echo $PreviousPage; ?>"><img src="images/album/rew.jpg" width="25" height="25" alt="Previous Page" border="0"></a><?php
						} ?>
					</td>
					<td>
						<input class="submit" type="submit" name="action" value="View"><?php
						if($CanDelete) {
							?>&nbsp;<input class="submit" type="submit" name="action" value="Delete"><?php
						}
						if($CanSave) {
							?>&nbsp;<input class="submit" type="submit" name="action" value="Save"><?php
						} ?>
					</td>
					<td style="width: 30%" valign="middle"><?php
						if(isset($NextPage)) { ?>
							<a href="<?php echo $NextPage; ?>"><img src="images/album/fwd.jpg" width="25" height="25" alt="Next Page" border="0"></a><?php
						} ?>
					</td>
				</tr>
			</table>
			<br /><br />
			<table class="standard fullwidth">
				<tr>
					<th class="shrink">View</th>
					<th class="shrink">Date</th>
					<th class="shrink">Sector</th>
					<th>Attacker</th>
					<th>Defender</th>
				</tr><?php
					foreach ($Logs as $LogID => $Log) { ?>
						<tr>
							<td class="center">
								<input type="checkbox" value="on" name="id[<?php echo $LogID; ?>]">
							</td>
							<td class="noWrap"><?php echo date(DATE_FULL_SHORT, $Log['Time']); ?></td>
							<td class="center"><?php echo $Log['Sector']; ?></td>
							<td><?php echo $Log['Attacker']; ?></td>
							<td><?php echo $Log['Defender']; ?></td>
						</tr><?php
					} ?>
				</table>
			</form><?php
		}
		else { ?>
			No combat logs found<?php
		} ?>
	</div><?php
} ?>