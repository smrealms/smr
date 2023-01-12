<?php declare(strict_types=1);

use Smr\Force;

?>
<div id="sector_forces" class="ajax"><?php
	if ($ThisSector->hasForces()) {
		// Display forces by increasing expiration time (soonest to expire first)
		$Forces = $ThisSector->getForces();
		uasort($Forces, fn($a, $b) => $a->getExpire() <=> $b->getExpire());

		$RefreshAny = false; ?>
		<table class="standard fullwidth csForces">
			<tbody>
				<tr>
					<th class="header" colspan="6">Forces (<?php echo count($Forces); ?>)</th>
				</tr>
				<tr>
					<th>Mines</th>
					<th>Combat</th>
					<th>Scout</th>
					<th>Expires</th>
					<th>Owner</th>
					<th>Option</th>
				</tr><?php
				foreach ($Forces as $Force) {
					$Owner = $Force->getOwner();
					$SharedForceAlliance = $Owner->sharedForceAlliance($ThisPlayer);
					if ($SharedForceAlliance) {
						$RefreshAny = true;
					} ?>

					<tr>
						<td class="center shrink noWrap"><?php
							if ($SharedForceAlliance && !$ThisShip->hasMaxMines() && $Force->hasMines()) {
								?><a href="<?php echo $Force->getTakeMineHREF() ?>">[-]</a><?php
							}
							echo $Force->getMines();
							if ($SharedForceAlliance && $ThisShip->hasMines() && !$Force->hasMaxMines()) {
								?><a href="<?php echo $Force->getDropMineHREF() ?>">[+]</a><?php
							} ?>
						</td>
						<td class="center shrink noWrap"><?php
							if ($SharedForceAlliance && !$ThisShip->hasMaxCDs() && $Force->hasCDs()) {
								?><a href="<?php echo $Force->getTakeCDHREF() ?>">[-]</a><?php
							}
							echo $Force->getCDs();
							if ($SharedForceAlliance && $ThisShip->hasCDs() && !$Force->hasMaxCDs()) {
								?><a href="<?php echo $Force->getDropCDHREF() ?>">[+]</a><?php
							} ?>
						</td>
						<td class="center shrink noWrap"><?php
							if ($SharedForceAlliance && !$ThisShip->hasMaxSDs() && $Force->hasSDs()) {
								?><a href="<?php echo $Force->getTakeSDHREF() ?>">[-]</a><?php
							}
							echo $Force->getSDs();
							if ($SharedForceAlliance && $ThisShip->hasSDs() && !$Force->hasMaxSDs()) {
								?><a href="<?php echo $Force->getDropSDHREF() ?>">[+]</a><?php
							} ?>
						</td>
						<td class="shrink noWrap center"><?php
							if ($SharedForceAlliance) {
								?><span class="green"><?php echo date($ThisAccount->getDateTimeFormat(), $Force->getExpire()); ?></span><?php
							} else {
								?><span class="red">WAR</span><?php
							} ?>
						</td>
						<td><?php echo $Owner->getLinkedDisplayName(); ?></td>
						<td class="shrink noWrap center"><?php
							if ($SharedForceAlliance) { ?>
								<div class="buttonA">
									<a class="buttonA" href="<?php echo $Force->getExamineDropForcesHREF(); ?>">Examine</a>
								</div>
								<div class="buttonA">
									<a href="<?php echo $Force->getRefreshHREF(); ?>" class="buttonA">Refresh</a>
								</div><?php
							} else { ?>
								<div class="buttonA">
									<a class="buttonA enemyExamine" href="<?php echo $Force->getAttackForcesHREF(); ?>">Attack (<?php echo $Force->getAttackTurnCost($ThisShip); ?>)</a>
								</div><?php
							} ?>
						</td>
					</tr><?php
				}
				if ($RefreshAny) { ?>
					<tr>
						<td class="center" colspan="6">
							<div class="buttonA"><a href="<?php echo Force::getRefreshAllHREF() ?>" class="buttonA">Refresh All</a></div>
						</td>
					</tr><?php
				} ?>
			</tbody>
		</table><?php
	} ?>
</div>
