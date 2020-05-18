<?php

if (count($Planets) > 0) { ?>
	<table id="planet-list" class="standard inset center">
		<thead>
			<tr>
				<th class="sort shrink" data-sort="sort_type">
					<a href="<?php echo WIKI_URL; ?>/game-guide/locations#planets" target="_blank"><img class="bottom" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a></th>
				<th class="sort shrink" data-sort="sort_name">Name</th>
				<th class="sort shrink" data-sort="sort_lvl">Level</th>
				<th class="sort shrink" data-sort="sort_owner">Owner</th>
				<th class="sort shrink" data-sort="sort_sector">Sector</th>

				<th class="sort shrink" data-sort="sort_credits">Credits</th>
				<th class="sort shrink" data-sort="sort_bonds">Bonds</th>
				<th class="sort shrink" data-sort="sort_interest">Interest Rate</th>
				<th class="sort" data-sort="sort_mature">Mature Time</th>
			</tr>
		</thead>

		<tbody class="list"><?php
			foreach ($Planets as $Planet) { ?>
				<tr id="planet-<?php echo $Planet->getSectorID(); ?>" class="ajax">
					<td class="sort_type shrink">
						<img src="<?php echo $Planet->getTypeImage(); ?>"  width="16" height="16" alt="" title="<?php echo $Planet->getTypename() . ': ' . $Planet->getTypeDescription(); ?>" /></td>
					<td class="sort_name left"><?php echo $Planet->getDisplayName(); ?></td>
					<td class="sort_lvl"><?php echo number_format($Planet->getLevel(), 2); ?></td>
					<td class="sort_owner noWrap left"><?php echo $Planet->getOwner()->getLinkedDisplayName(false); ?></td>
					<td class="sort_sector"><a href="<?php echo Globals::getPlotCourseHREF($ThisPlayer->getSectorID(), $Planet->getSectorID()); ?>"><?php echo $Planet->getSectorID(); ?></a>&nbsp;(<a href="<?php echo $Planet->getGalaxy()->getGalaxyMapHREF(); ?>" target="gal_map"><?php echo $Planet->getGalaxy()->getName(); ?></a>)</td>

					<?php
					if ($Planet->hasMenuOption('FINANCE')) { ?>
						<td class="sort_credits"><?php echo number_format($Planet->getCredits()); ?></td>
						<td class="sort_bonds"><?php echo number_format($Planet->getBonds()); ?></td>
						<td class="sort_interest"><?php echo $Planet->getInterestRate() * 100; ?>%</td>
						<?php
						if ($Planet->getBonds() > 0) {
							$matureTime = $Planet->getMaturity() - TIME; ?>
							<td class="sort_mature noWrap" data-sort_mature="<?php echo $matureTime; ?>">
								<?php echo format_time($matureTime, true); ?>
							</td><?php
						} else { ?>
							<td></td><?php
						}
					} else { ?>
						<td colspan="4">This planet has no economic infrastructure!</td><?php
					} ?>
				</tr><?php
			} ?>
		</tbody>
	</table><br/>
	<?php $this->setListjsInclude('PlanetListFinancial');
} ?>
