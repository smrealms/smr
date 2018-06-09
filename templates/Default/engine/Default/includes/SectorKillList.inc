<table class="standard" width="45%">
	<tr>
		<th class="shrink">Rank</th>
		<th>Sector</th>
		<th>Battles</th>
	</tr><?php
	foreach ($Rankings as $Rank => $RankSector) { ?>
		<tr>
			<td class="center<?php
				if ($ThisPlayer->getSectorID() == $RankSector->getSectorID()) {
					echo ' bold';
				} ?>"><?php echo $Rank; ?>
			</td>

			<td class="center<?php
				if ($ThisPlayer->getSectorID() == $RankSector->getSectorID()) {
					echo ' bold';
				} ?>"><?php echo $RankSector->getSectorID(); ?>
			</td>

			<td class="center<?php
				if ($ThisPlayer->getSectorID() == $RankSector->getSectorID()) {
					echo ' bold';
				} ?>"><?php echo $RankSector->getBattles(); ?>
			</td>
		</tr><?php
	} ?>
</table>
