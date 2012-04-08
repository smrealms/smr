<div id="sector_forces" class="ajax"><?php
	if($ThisSector->hasForces()) {
		$Forces =& $ThisSector->getForces();
		Sorter::sortByNumMethod($Forces,'getExpire');
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
				foreach($Forces as $Force) {
					$Owner =& $Force->getOwner();
					$SharedForceAlliance = $Owner->sharedForceAlliance($ThisPlayer);
					if($SharedForceAlliance) {
						$RefreshAny = true;
					} ?>
				
					<tr>
						<td class="center shrink noWrap"><?php
							if($SharedForceAlliance && $ThisShip->canAcceptMines()&&$Force->hasMines()) {
								?><a href="<?php echo $Force->getTakeMineHREF() ?>">[-]</a><?php
							}
							echo$Force->getMines();
							if($SharedForceAlliance && $ThisShip->hasMines()&&$Force->canAcceptMines()) {
								?><a href="<?php echo $Force->getDropMineHREF() ?>">[+]</a><?php
							} ?>
						</td>
						<td class="center shrink noWrap"><?php
							if($SharedForceAlliance && $ThisShip->canAcceptCDs()&&$Force->hasCDs()) {
								?><a href="<?php echo $Force->getTakeCDHREF() ?>">[-]</a><?php
							}
							echo $Force->getCDs();
							if($SharedForceAlliance && $ThisShip->hasCDs()&&$Force->canAcceptCDs()) {
								?><a href="<?php echo $Force->getDropCDHREF() ?>">[+]</a><?php
							} ?>
						</td>
						<td class="center shrink noWrap"><?php
							if($SharedForceAlliance && $ThisShip->canAcceptSDs()&&$Force->hasSDs()) {
								?><a href="<?php echo $Force->getTakeSDHREF() ?>">[-]</a><?php
							}
							echo $Force->getSDs();
							if($SharedForceAlliance && $ThisShip->hasSDs()&&$Force->canAcceptSDs()) {
								?><a href="<?php echo $Force->getDropSDHREF() ?>">[+]</a><?php
							} ?>
						</td>
						<td class="shrink noWrap center"><?php
							if($SharedForceAlliance) {
								?><span class="green"><?php echo date(DATE_FULL_SHORT,$Force->getExpire());?></span><?php
							}
							else {
								?><span class="red">WAR</span><?php
							} ?>
						</td>
						<td><?php
//							if($SharedForceAlliance)
//							{
								echo $Owner->getLinkedDisplayName();
//							}
//							else
//							{
//								<span class="red">UNKNOWN</span>
//							} ?>
						</td>
						<td align="center" class="shrink center">
							
							<div class="buttonA">
								<a href="<?php
									if($SharedForceAlliance) {
										echo $Force->getExamineDropForcesHREF();
									}
									else {
										echo $Force->getExamineAttackForcesHREF();
									} ?>" class="buttonA<?php if(!$SharedForceAlliance){ ?> enemyExamine<?php } ?>"> Examine </a>
							</div><?php
							if($SharedForceAlliance) { ?>
								<br />
								<br />
								<div class="buttonA">
									<a href="<?php echo $Force->getRefreshHREF() ?>" class="buttonA"> Refresh </a>
								</div><?php
							} ?>
						</td>
					</tr><?php
				}
				if($RefreshAny) { ?>
					<tr>
						<td class="center" colspan="6">
							<div class="buttonA"><a href="<?php echo $Force->getRefreshAllHREF() ?>" class="buttonA"> Refresh All </a></div>
						</td>
					</tr><?php
				} ?>
			</tbody>
		</table><?php
	} ?>
</div>