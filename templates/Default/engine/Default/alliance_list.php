<div align="center"><?php
	if (!$ThisPlayer->hasAlliance()) { ?>
		<div class="buttonA"><a class="buttonA" href="<?php echo $CreateAllianceHREF; ?>">&nbsp;Create your own alliance!&nbsp;</a></div>
		<br /><br /><?php
	}
	if (count($Alliances) > 0) { ?>
		<table class="standard inset">
			<thead>
				<tr>
					<th>
						<a class="header" href="<?php echo $SortNameHREF; ?>">Alliance Name</a>
					</th>
					<th class="shrink">
						<a class="header" href="<?php echo $SortTotalExpHREF; ?>">Total Experience</a>
					</th>
					<th class="shrink">
						<a class="header" href="<?php echo $SortAvgExpHREF; ?>">Average Experience</a>
					</th>
					<th class="shrink">
						<a class="header" href="<?php echo $SortMembersHREF; ?>">Members</a>
						
					</th>
				</tr>
			</thead>

			<tbody><?php
				foreach($Alliances as $AllianceID => $Alliance) { ?>
					<tr>
						<td>
							<a href="<?php echo $Alliance['ViewHREF']; ?>"><?php echo $Alliance['Name']; ?></a>
						</td>
						<td class="right"><?php echo number_format($Alliance['TotalExperience']); ?></td>
						<td class="right"><?php echo number_format($Alliance['AverageExperience']); ?></td>
						<td class="right"><?php echo number_format($Alliance['Members']); ?></td>
					</tr><?php
				} ?>
			</tbody>
		</table><br />Click column table to reorder!<?php
	}
	else { ?>
		Currently there are no alliances.<?php
	} ?>
</div>