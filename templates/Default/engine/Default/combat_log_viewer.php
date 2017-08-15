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
			There <span id="total-logs"><?php echo $this->pluralise('is', $TotalLogs), ' ', $TotalLogs, ' ', $LogType, $this->pluralise(' log', $NumLogs); ?></span> available for viewing of which <?php echo $NumLogs, ' ', $this->pluralise('is', $NumLogs); ?> being shown.<br /><br />
			<form class="standard" method="POST" action="<?php echo $LogFormHREF; ?>">
				<table class="fullwidth center">
					<tr>
						<td id="prev" class="ajax" style="width: 30%" valign="middle"><?php
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
						<td id="next" class="ajax" style="width: 30%" valign="middle"><?php
							if(isset($NextPage)) { ?>
								<a href="<?php echo $NextPage; ?>"><img src="images/album/fwd.jpg" width="25" height="25" alt="Next Page" border="0"></a><?php
							} ?>
						</td>
					</tr>
				</table>
				<br /><br />
				<table id="logs-list" class="standard fullwidth">
					<thead>
						<tr>
							<th class="shrink">View</th>
							<th class="sort shrink" data-sort="date">Date</th>
							<th class="sort shrink" data-sort="sectorid">Sector</th>
							<th class="sort" data-sort="attacker">Attacker</th>
							<th class="sort" data-sort="defender">Defender</th>
						</tr>
					</thead>
					<tbody class="list"><?php
						foreach ($Logs as $LogID => $Log) { ?>
							<tr id="log-<?php echo $LogID; ?>" class="ajax">
								<td class="center">
									<input type="checkbox" value="on" name="id[<?php echo $LogID; ?>]">
								</td>
								<td class="date noWrap"><?php echo date(DATE_FULL_SHORT, $Log['Time']); ?></td>
								<td class="sectorid center"><?php echo $Log['Sector']; ?></td>
								<td class="attacker"><?php echo $Log['Attacker']; ?></td>
								<td class="defender"><?php echo $Log['Defender']; ?></td>
							</tr><?php
						} ?>
					</tbody>
				</table>
			</form>
			<script type="text/javascript" src="js/list.1.0.0.custom.min.js"></script>
			<script>
			var list = new List('logs-list', {
				valueNames: ['date', 'sectorid', 'attacker', 'defender'],
				sortFunction: function(a, b) {
					return list.sort.naturalSort(a.values()[this.valueName].replace(/<.*?>|,/g,''), b.values()[this.valueName].replace(/<.*?>|,/g,''), this);
				}
			});
			</script><?php
		}
		else { ?>
			No <?php echo $LogType; ?> combat logs found<?php
		} ?>
	</div><?php
} ?>
