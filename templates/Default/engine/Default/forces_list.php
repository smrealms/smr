<?php
if (empty($Forces)) { ?>
	You have no deployed forces.
	<a href="<?php echo WIKI_URL; ?>/game-guide/forces" target="_blank">
		<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Forces"/>
	</a><?php
} else { ?>
	<table id="forces-list" class="standard inset">
		<a href="<?php echo WIKI_URL; ?>/game-guide/forces" target="_blank">
			<img align="right" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Forces"/>
		</a><br />

		<thead>
		<tr>
			<th class="sort shrink" data-sort="sort_sector">Sector ID</th>
			<th class="sort shrink" data-sort="sort_cds">Combat Drones</th>
			<th class="sort shrink" data-sort="sort_sds">Scout Drones</th>
			<th class="sort shrink" data-sort="sort_mines">Mines</th>
			<th class="sort shrink" data-sort="sort_expire">Expire Time</th>
		</tr>
		</thead>

		<tbody class="list"><?php
		foreach ($Forces as $Force) { ?>
			<tr>
				<td class="sort_sector noWrap"><?php echo $Force->getSectorID(); ?> (<?php echo $Force->getGalaxy()->getName(); ?>)</td>
				<td class="sort_cds center"><?php echo $Force->getCDs(); ?></td>
				<td class="sort_sds center"><?php echo $Force->getSDs(); ?></td>
				<td class="sort_mines center"><?php echo $Force->getMines(); ?></td>
				<td class="sort_expire noWrap" data-expire="<?php echo $Force->getExpire(); ?>"><?php echo date(DATE_FULL_SHORT, $Force->getExpire()); ?></td>
			</tr><?php
		} ?>
		</tbody>
	</table>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
	<script>
	var list = new List('forces-list', {
		valueNames: ['sort_sector', 'sort_cds', 'sort_sds', 'sort_mines', {name: 'sort_expire', attr: 'data-expire'}],
		sortFunction: function(a, b, options) {
			return list.utils.naturalSort(a.values()[options.valueName].replace(/<.*?>|,/g,''), b.values()[options.valueName].replace(/<.*?>|,/g,''), options);
		}
	});
	</script><?php
}
