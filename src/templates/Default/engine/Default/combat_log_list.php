<?php declare(strict_types=1);

/**
 * @var Smr\Account $ThisAccount
 * @var Smr\Template $this
 * @var string $LogType
 * @var int $TotalLogs
 * @var ?bool $CanSave
 * @var ?bool $CanDelete
 * @var ?string $LogFormHREF
 * @var array<int, array{Attacker: string, Defender: string, Time: int, Sector: int}> $Logs
 */

if (isset($Message)) {?>
	<div class="center"><?php echo $Message; ?></div><br /><?php
} ?>

<div class="center"><?php
	$NumLogs = count($Logs);
	if ($NumLogs > 0) { ?>
		You have <span id="total-logs"><?php echo pluralise($TotalLogs, $LogType . ' log'); ?></span> available for viewing (<?php echo $NumLogs; ?> shown).<br /><br />
		<form class="standard" method="POST" action="<?php echo $LogFormHREF; ?>">
			<table class="fullwidth center">
				<tr>
					<td id="prev" class="ajax" style="width: 30%" valign="middle"><?php
						if (isset($PreviousPage)) { ?>
							<a href="<?php echo $PreviousPage; ?>"><img src="images/album/rew.jpg" width="25" height="25" alt="Previous Page" border="0"></a><?php
						} ?>
					</td>
					<td>
						<input type="submit" name="action" value="View"><?php
						if ($CanDelete) {
							?>&nbsp;<input type="submit" name="action" value="Delete"><?php
						}
						if ($CanSave) {
							?>&nbsp;<input type="submit" name="action" value="Save"><?php
						} ?>
					</td>
					<td id="next" class="ajax" style="width: 30%" valign="middle"><?php
						if (isset($NextPage)) { ?>
							<a href="<?php echo $NextPage; ?>"><img src="images/album/fwd.jpg" width="25" height="25" alt="Next Page" border="0"></a><?php
						} ?>
					</td>
				</tr>
			</table>
			<br /><br />
			<table id="logs-list" class="standard inset centered">
				<thead>
					<tr>
						<th class="shrink">View</th>
						<th class="sort shrink" data-sort="sort_date">Date</th>
						<th class="sort shrink" data-sort="sort_sectorid">Sector</th>
						<th class="sort" data-sort="sort_attacker">Attacker</th>
						<th class="sort" data-sort="sort_defender">Defender</th>
					</tr>
				</thead>
				<tbody class="list"><?php
					foreach ($Logs as $LogID => $Log) { ?>
						<tr id="log-<?php echo $LogID; ?>" class="ajax">
							<td class="center">
								<input type="checkbox" value="on" name="id[<?php echo $LogID; ?>]">
							</td>
							<td class="sort_date noWrap"><?php echo date($ThisAccount->getDateTimeFormat(), $Log['Time']); ?></td>
							<td class="sort_sectorid center"><?php echo $Log['Sector']; ?></td>
							<td class="sort_attacker"><?php echo $Log['Attacker']; ?></td>
							<td class="sort_defender"><?php echo $Log['Defender']; ?></td>
						</tr><?php
					} ?>
				</tbody>
			</table>
		</form>
		<?php $this->listjsInclude = 'combat_log_list';
	} else { ?>
		No <?php echo $LogType; ?> combat logs found<?php
	} ?>
</div>
