<table class="lmt centered"><?php
	$GalaxyMap = isset($GalaxyMap) && $GalaxyMap;
	$UniGen = isset($UniGen) ? $UniGen : false;
	$MapPlayer = $UniGen ? null : $ThisPlayer;
	$MovementTypes = array('Up', 'Left', 'Right', 'Down');
	foreach ($MapSectors as $MapSector) { ?>
		<tr><?php
			foreach ($MapSector as $Sector) {
				$isCurrentSector = !$UniGen && $ThisSector->equals($Sector);
				$isLinkedSector = !$UniGen && $ThisSector->isLinkedSector($Sector);
				$isSeedlistSector = !$UniGen && isset($ShowSeedlistSectors) && $ShowSeedlistSectors && $MapPlayer->getAlliance()->isInSeedlist($Sector);
				$isVisited = $Sector->isVisited($MapPlayer); ?>
				<td id="sector<?php echo $Sector->getSectorID(); ?>" class="ajax">
					<div class="lm_sector galaxy<?php echo $Sector->getGalaxyID();
						if ($isSeedlistSector) {
							if ($isCurrentSector) { ?> currentSeclm_seedlist<?php } else { ?> lm_seedlist<?php }
						}
						if ($isCurrentSector) { ?> currentSeclm<?php
						} else if ($isLinkedSector && !$isVisited) { ?> connectSeclmu<?php
						} else if ($isLinkedSector) { ?> connectSeclm<?php
						} else if ($isVisited) { ?> normalSeclm<?php
						} else { ?> normalSeclmu<?php } ?>"><?php

						if ($isVisited) {
							if (isset($ToggleLink)) {
								$ToggleLink['sector_id'] = $Sector->getSectorID();
							}
							foreach ($MovementTypes as $MovementType) {
								if (isset($ToggleLink)) {
									$ToggleLink['dir'] = $MovementType; ?>
									<a onclick="ajaxLink('<?php echo SmrSession::getNewHREF($ToggleLink); ?>')" class="lm<?php echo $MovementType; ?>"><?php
								} ?>
								<div class="lm<?php echo $MovementType; ?> <?php if ($Sector->getLink($MovementType)) { ?>con<?php } else { ?>wall<?php } ?>"></div><?php
								if (isset($ToggleLink)) { ?>
									</a><?php
								}
							}
							if ($Sector->hasLocation() || $Sector->hasPlanet()) { ?>
								<div class="lmloc"><?php
								if ($Sector->hasLocation()) {
									foreach ($Sector->getLocations() as $Location) {
										if ($isCurrentSector && $Location->hasAction() && !$GalaxyMap) {
											?><a href="<?php echo $Location->getExamineHREF() ?>"><?php
										} ?>
											<img src="<?php echo $Location->getImage() ?>" width="16" height="16" alt="<?php echo $Location->getName() ?>" title="<?php echo $Location->getName() ?>" /><?php
										if ($isCurrentSector && $Location->hasAction() && !$GalaxyMap) { ?></a><?php }
									}
								}
								if ($Sector->hasPlanet()) {
									$planet = $Sector->getPlanet();
									if ($isCurrentSector && !$GalaxyMap) {
										?><a href="<?php echo $planet->getExamineHREF(); ?>"><?php
									} ?>
									<img title="<?php echo $planet->getTypeName() ?>" alt="Planet" src="<?php echo $planet->getTypeImage() ?>" width="16" height="16"/><?php
									if ($isCurrentSector && !$GalaxyMap) { ?></a><?php }
								} ?>
								</div><?php
							}
							if ((($UniGen || $isCurrentSector) && $Sector->hasPort()) || $Sector->hasCachedPort($MapPlayer)) {
								if (($UniGen || $isCurrentSector) && $Sector->hasPort()) {
									$Port = $Sector->getPort();
								} else if ($Sector->hasCachedPort($MapPlayer)) {
									$Port = $Sector->getCachedPort($MapPlayer);
								} ?>
								<div class="lmport <?php if ($Sector->getLinkLeft()) { ?>a<?php } else { ?>b<?php } ?> 
									"><?php
									if ($isCurrentSector && !$GalaxyMap) {
										?><a href="<?php echo Globals::getTradeHREF(); ?>"><?php
									} ?>
										<img src="images/port/buy.png" width="5" height="16" alt="Buy (<?php echo $Port->getRaceName(); ?>)" 
											title="Buy (<?php echo $Port->getRaceName(); ?>)" class="port<?php echo $Port->getRaceID(); ?>"/><?php
											foreach ($Port->getVisibleGoodsSold($MapPlayer) as $GoodID) {
												$Good = Globals::getGood($GoodID); ?>
												<img src="<?php echo $Good['ImageLink']; ?>" width="13" height="16" title="<?php echo $Good['Name'] ?>" alt="<?php echo $Good['Name']; ?>" /><?php
											} ?><br />
										<img src="images/port/sell.png" width="5" height="16" alt="Sell (<?php echo $Port->getRaceName(); ?>)" 
										title="Sell (<?php echo $Port->getRaceName(); ?>)" class="port<?php echo $Port->getRaceID(); ?>"/><?php
											foreach ($Port->getVisibleGoodsBought($MapPlayer) as $GoodID) {
												$Good = Globals::getGood($GoodID); ?>
												<img src="<?php echo $Good['ImageLink']; ?>" width="13" height="16" title="<?php echo $Good['Name'] ?>" alt="<?php echo $Good['Name']; ?>" /><?php
											}
									if ($isCurrentSector && !$GalaxyMap) { ?></a><?php } ?>
								</div><?php
							}
						}
						if (($isVisited && $Sector->hasWarp()) || (!$UniGen && ($MapPlayer->isPartOfCourse($Sector) || ($MapPlayer->hasPlottedCourse() && $isCurrentSector)))) { ?>
							<div class="lmp"><?php
								if (!$UniGen && ($MapPlayer->isPartOfCourse($Sector) || ($MapPlayer->hasPlottedCourse() && $isCurrentSector))) {
									?><img title="Course" alt="Course" src="images/plot_icon.gif" width="16" height="16"/><?php
								}
								if ($isVisited) {
									if ($Sector->hasWarp()) {
										if ($GalaxyMap) { ?><a href="<?php echo $Sector->getWarpSector()->getGalaxyMapHREF(); ?>"><?php } else if ($isCurrentSector) { ?><a href="<?php echo $Sector->getWarpSector()->getLocalMapMoveHREF(); ?>"><?php } ?>
											<img title="Warp to #<?php echo $Sector->getWarp(); ?> (<?php echo $Sector->getWarpSector()->getGalaxyName(); ?>)" alt="Warp to #<?php echo $Sector->getWarp(); ?>" src="images/warp.png" width="16" height="16" /><?php
										if ($isCurrentSector || $GalaxyMap) { ?></a><?php }
									}
								} ?>
							</div><?php
						}
						if (!$UniGen) {
							$CanScanSector = ($ThisShip->hasScanner() && $isLinkedSector) || $isCurrentSector;
							$ShowFriendlyForces = isset($HideAlliedForces) && $HideAlliedForces ?
							                      $Sector->hasPlayerForces($MapPlayer) : $Sector->hasFriendlyForces($MapPlayer);
							if (($CanScanSector && ($Sector->hasForces() || $Sector->hasPlayers())) || $ShowFriendlyForces || $Sector->hasFriendlyTraders($MapPlayer)) { ?>
								<div class="lmtf"><?php
									if ($CanScanSector && $Sector->hasEnemyTraders($MapPlayer)) {
										?><img class="enemyBack" title="Enemy Trader" alt="Enemy Trader" src="images/trader.png" width="13" height="16"/><?php
									}
									if ($CanScanSector && $Sector->hasProtectedTraders($MapPlayer)) {
										?><img class="neutralBack" title="Protected Trader" alt="Protected Trader" src="images/trader.png" width="13" height="16"/><?php
									}
									if ($Sector->hasAllianceFlagship($MapPlayer) && !$MapPlayer->isFlagship()) {
										?><img class="friendlyBack" title="Alliance Flagship" alt="Alliance Flagship" src="images/flagship.png" width="16" height="16" /><?php
									}
									if ($Sector->hasFriendlyTraders($MapPlayer)) {
										?><img class="friendlyBack" title="Friendly Trader" alt="Friendly Trader" src="images/trader.png" width="13" height="16"/><?php
									}
									if ($Sector->hasForces()) {
										if ($CanScanSector && $Sector->hasEnemyForces($MapPlayer)) {
											?><img class="enemyBack" title="Enemy Forces" alt="Enemy Forces" src="images/forces.png" width="13" height="16"/><?php
										}
										if ($ShowFriendlyForces) {
											?><img class="friendlyBack" title="Friendly Forces" alt="Friendly Forces" src="images/forces.png" width="13" height="16"/><?php
										}
									} ?>
								</div><?php
							}
						} ?>
						<div class="lmsector"><?php echo $Sector->getSectorID(); ?></div><?php
						if ($UniGen) {
							$UniGen['sector_id'] = $Sector->getSectorID(); ?>
							<a class="move_hack" href="<?php echo SmrSession::getNewHREF($UniGen); ?>"></a><?php
						} else if ($GalaxyMap) { ?>
							<a class="move_hack" href="<?php echo $Sector->getGalaxyMapHREF(); ?>"></a><?php
						} else if ($isLinkedSector) { ?>
							<a class="move_hack" href="<?php echo $Sector->getLocalMapMoveHREF(); ?>"></a><?php
						} else if ($isCurrentSector) { ?>
							<a class="move_hack" href="<?php echo Globals::getCurrentSectorHREF(); ?>"></a><?php
						} ?>
					</div>
				</td><?php
			} ?>
		</tr><?php
		// NOTE: We no longer clear the caches here because we pre-cache.
		// If memory becomes an issue, we can implement a purge of the cache
		// for sectors that we have already processed.
	} ?>
</table>
