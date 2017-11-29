<?php
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
		<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
		<script>
		var list = new List('logs-list', {
			valueNames: ['date', 'sectorid', 'attacker', 'defender'],
			sortFunction: function(a, b, options) {
				return list.utils.naturalSort(a.values()[options.valueName].replace(/<.*?>|,/g,''), b.values()[options.valueName].replace(/<.*?>|,/g,''), options);
			}
		});
		</script><?php
	}
	else { ?>
		No <?php echo $LogType; ?> combat logs found<?php
	} ?>
</div>
