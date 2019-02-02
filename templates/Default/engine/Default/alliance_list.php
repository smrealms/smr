<div align="center"><?php
	if (!$ThisPlayer->hasAlliance()) { ?>
		<div class="buttonA"><a class="buttonA" href="<?php echo $CreateAllianceHREF; ?>">Create your own alliance!</a></div>
		<br /><br /><?php
	}
	if (count($Alliances) > 0) { ?>
		<table id="alliance-list" class="standard inset">
			<thead>
				<tr>
					<th class="sort" data-sort="name">Alliance Name</th>
					<th class="sort shrink" data-sort="totExp">Total Experience</th>
					<th class="sort shrink" data-sort="avgExp">Average Experience</th>
					<th class="sort shrink" data-sort="members">Members</th>
				</tr>
			</thead>

			<tbody class="list"><?php
				foreach($Alliances as $AllianceID => $Alliance) { ?>
					<tr id="alliance-<?php echo $AllianceID; ?>" class="ajax">
						<td class="name">
							<a href="<?php echo $Alliance['ViewHREF']; ?>"><?php echo $Alliance['Name']; ?></a>
						</td>
						<td class="totExp right"><?php echo number_format($Alliance['TotalExperience']); ?></td>
						<td class="avgExp right"><?php echo number_format($Alliance['AverageExperience']); ?></td>
						<td class="members right"><?php echo number_format($Alliance['Members']); ?></td>
					</tr><?php
				} ?>
			</tbody>
		</table><br />Click column table to reorder!
		<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
		<script>
		var list = new List('alliance-list', {
			valueNames: ['name', 'totExp', 'avgExp', 'members'],
			sortFunction: function(a, b, options) {
				return list.utils.naturalSort(a.values()[options.valueName].replace(/<.*?>|,/g,''), b.values()[options.valueName].replace(/<.*?>|,/g,''), options);
			}
		});
		</script><?php
	}
	else { ?>
		Currently there are no alliances.<?php
	} ?>
</div>
