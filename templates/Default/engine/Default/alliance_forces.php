<?php
if (empty($Forces)) { ?>
	Your alliance has no deployed forces.
	<a href="<?php echo WIKI_URL; ?>/game-guide/forces" target="_blank">
		<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Forces"/>
	</a><?php
} else { ?>
	<div class="center">
		Your alliance currently has <?php echo count($Forces); ?> stacks of forces in the universe.
		<a href="<?php echo WIKI_URL; ?>/game-guide/forces" target="_blank">
			<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Forces"/>
		</a>
	</div>
	<br /><br />

	<table class="standard centered">
		<tr>
			<th>Number of Force</th>
			<th>Value</th>
		</tr>
		<tr>
			<td><span class="yellow"><?php echo number_format($Total['Mines']); ?></span> mines</td>
			<td><span class="creds"><?php echo number_format($TotalCost['Mines']); ?></span> credits</td>
		</tr>
		<tr>
			<td><span class="yellow"><?php echo number_format($Total['CDs']); ?></span> combat drones</td>
			<td><span class="creds"><?php echo number_format($TotalCost['CDs']); ?></span> credits</td>
		</tr>
		<tr>
			<td><span class="yellow"><?php echo number_format($Total['SDs']); ?></span> scout drones</td>
			<td><span class="creds"><?php echo number_format($TotalCost['SDs']); ?></span> credits</td>
		</tr>
		<tr>
			<td><span class="yellow bold"><?php echo number_format(array_sum($Total)); ?></span> forces</td>
			<td><span class="creds bold"><?php echo number_format(array_sum($TotalCost)); ?></span> credits</td>
		</tr>
	</table>
	<br />

	<table id="forces-list" class="standard inset centered">
	<thead>
	<tr>
		<th class="sort" data-sort="sort_name">Player Name</th>
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
			<td class="sort_name"><?php echo $Force->getOwner()->getLinkedDisplayName(false); ?></td>
			<td class="sort_sector noWrap"><?php echo $Force->getSectorID(); ?> (<?php echo $Force->getGalaxy()->getName(); ?>)</td>
			<td class="sort_cds center"><?php echo $Force->getCDs(); ?></td>
			<td class="sort_sds center"><?php echo $Force->getSDs(); ?></td>
			<td class="sort_mines center"><?php echo $Force->getMines(); ?></td>
			<td class="sort_expire noWrap" data-expire="<?php echo $Force->getExpire(); ?>"><?php echo date(DATE_FULL_SHORT, $Force->getExpire()); ?></td>
		</tr><?php
	} ?>
	</table>

	<?php $this->setListjsInclude('alliance_forces');
} ?>
