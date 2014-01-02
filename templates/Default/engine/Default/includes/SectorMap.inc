<table class="lmt centered"><?php
	$GalaxyMap = isset($GalaxyMap)&&$GalaxyMap;
	$UniGen = isset($UniGen) ? $UniGen : false;
	$ThisPlayer = $UniGen ? null : $ThisPlayer;
	foreach($MapSectors as &$MapSector) { ?>
		<tr><?php
			foreach($MapSector as &$Sector) {
				$isCurrentSector = !$UniGen && $ThisSector->equals($Sector);
				$isLinkedSector = !$UniGen && $ThisSector->isLinkedSector($Sector);
				$isVisited = $Sector->isVisited($ThisPlayer); ?>
				<td id="sector<?php echo $Sector->getSectorID(); ?>" class="ajax lm_sector <?php if($isCurrentSector){ ?>currentSeclm<?php }else if($isLinkedSector){ ?>connectSeclm<?php }else if($isVisited){ ?>normalSeclm<?php }else{ ?>normalSeclmu<?php } ?>"><?php
					if($isVisited) { ?>
						<div class="lmup <?php if($Sector->getLinkUp()) { ?>hcon<?php } else { ?>wall<?php } ?>"></div>
						<div class="lmleft <?php if($Sector->getLinkLeft()) { ?>vcon<?php } else { ?>wall<?php } ?>"></div>
						<div class="lmright <?php if($Sector->getLinkRight()) { ?>vcon<?php } else { ?>wall<?php } ?>"></div>
						<div class="lmdown <?php if($Sector->getLinkDown()){ ?>hcon<?php } else { ?>wall<?php } ?>"></div><?php
						if($Sector->hasLocation() || $Sector->hasPlanet()) { ?>
							<div class="lmloc"><?php
							if($Sector->hasLocation()) {
								foreach($Sector->getLocations() as $Location) {
									if($isCurrentSector && $Location->hasAction() && !$GalaxyMap) {
										?><a href="<?php echo $Location->getExamineHREF() ?>"><?php
									} ?>
										<img src="<?php echo $Location->getImage() ?>" width="16" height="16" alt="<?php echo $Location->getName() ?>" title="<?php echo $Location->getName() ?>" /><?php
									if($isCurrentSector && $Location->hasAction() && !$GalaxyMap){ ?></a><?php }
								}
							}
							if($Sector->hasPlanet()) {
								if($isCurrentSector && !$GalaxyMap) {
									?><a href="<?php echo $Sector->getPlanet()->getExamineHREF(); ?>"><?php
								} ?>
								<img title="Planet" alt="Planet" src="images/planet.gif" width="16" height="16"/><?php
								if($isCurrentSector && !$GalaxyMap){ ?></a><?php }
							} ?>
							</div><?php
						}
						if((($UniGen || $isCurrentSector) && $Sector->hasPort()) || $Sector->hasCachedPort($ThisPlayer)) {
							if(($UniGen || $isCurrentSector) && $Sector->hasPort()) {
								$Port =& $Sector->getPort();
							}
							else if($Sector->hasCachedPort($ThisPlayer)) {
								$Port =& $Sector->getCachedPort($ThisPlayer);
							} ?>
							<div class="lmport <?php if($Sector->getLinkLeft()) { ?>a<?php } else { ?>b<?php } ?>"><?php
								if($isCurrentSector && !$GalaxyMap) {
									?><a href="<?php echo Globals::getTradeHREF(); ?>"><?php
								} ?>
									<img src="images/port/buy.gif" width="5" height="16" alt="Buy (<?php echo $Port->getRaceName(); ?>)" title="Buy (<?php echo $Port->getRaceName(); ?>)" /><?php
										foreach($Port->getVisibleGoodsSold($ThisPlayer) as $Good) { ?>
											<img src="<?php echo $Good['ImageLink']; ?>" width="13" height="16" title="<?php echo $Good['Name'] ?>" alt="<?php echo $Good['Name']; ?>" /><?php
										} ?><br />
									<img src="images/port/sell.gif" width="5" height="16" alt="Sell (<?php echo $Port->getRaceName(); ?>)" title="Sell (<?php echo $Port->getRaceName(); ?>)" /><?php
										foreach($Port->getVisibleGoodsBought($ThisPlayer) as $Good) { ?>
											<img src="<?php echo $Good['ImageLink']; ?>" width="13" height="16" title="<?php echo $Good['Name'] ?>" alt="<?php echo $Good['Name']; ?>" /><?php
										}
								if($isCurrentSector && !$GalaxyMap){ ?></a><?php } ?>
							</div><?php
						}
					}
					if(($isVisited && ($Sector->hasWarp() || $Sector->hasMine())) || (!$UniGen && ($ThisPlayer->isPartOfCourse($Sector) || ($ThisPlayer->hasPlottedCourse()&&$isCurrentSector)))) { ?>
						<div class="lmp"><?php
							if(!$UniGen && ($ThisPlayer->isPartOfCourse($Sector) || ($ThisPlayer->hasPlottedCourse()&&$isCurrentSector))) {
								?><img title="Course" alt="Course" src="images/plot_icon.gif" width="16" height="16"/><?php
							}
							if($isVisited) {
								if($Sector->hasWarp()) {
									if($GalaxyMap){ ?><a href="<?php echo $Sector->getWarpSector()->getGalaxyMapHREF(); ?>"><?php }
									else if($isCurrentSector){ ?><a href="<?php echo $Sector->getWarpSector()->getLocalMapHREF(); ?>"><?php } ?>
										<img title="Warp to #<?php echo $Sector->getWarp(); ?> (<?php echo $Sector->getWarpSector()->getGalaxyName(); ?>)" alt="Warp to #<?php echo $Sector->getWarp(); ?>" src="images/warp.png" width="16" height="16" /><?php
									if($isCurrentSector || $GalaxyMap){ ?></a><?php }
								}
								if($Sector->hasMine()) {
									?><img src="images/asteroid.gif" width="16" height="16" alt="Mining Available Here" title="Mining Available Here" /><?php
								}
							} ?>
						</div><?php
					}
					$CanScanSector = $UniGen || ($ThisShip->hasScanner() && $isLinkedSector) || $isCurrentSector;
					if( ($CanScanSector && ($Sector->hasForces() || $Sector->hasPlayers()) ) || $Sector->hasFriendlyForces($ThisPlayer) || $Sector->hasFriendlyTraders($ThisPlayer)) { ?>
						<div class="lmtf"><?php
							if(!$UniGen && (($isCurrentSector && $Sector->hasOtherTraders($ThisPlayer)) || ($ThisShip->hasScanner() && $isLinkedSector && $Sector->hasPlayers()) || $Sector->hasFriendlyTraders($ThisPlayer))) {
								if($CanScanSector && $Sector->hasEnemyTraders($ThisPlayer)) {
									?><img title="Enemy Trader" alt="Enemy Trader" src="images/enemyTrader.png" width="13" height="16"/><?php
								}
								if($Sector->hasFriendlyTraders($ThisPlayer)) {
									?><img title="Friendly Trader" alt="Friendly Trader" src="images/friendlyTrader.png" width="13" height="16"/><?php
								}
							}
							if($Sector->hasForces()) {
								if($CanScanSector && $Sector->hasEnemyForces($ThisPlayer)) {
									?><img title="Enemy Forces" alt="Enemy Forces" src="images/enemyForces.png" width="13" height="16"/><?php
								}
								if($Sector->hasFriendlyForces($ThisPlayer)) {
									?><img title="Friendly Forces" alt="Friendly Forces" src="images/friendlyForces.png" width="13" height="16"/><?php
								}
								/*?><img title="Forces" alt="Forces" src="images/forces.jpg"/><?php*/
							} ?>
						</div><?php
					} ?>
					<div class="lmsector <?php if($isVisited){ ?>visited<?php }else{ ?>unvisited<?php } ?>"><?php echo $Sector->getSectorID(); ?></div><?php
					if($UniGen) {
						$UniGen['sector_id'] = $Sector->getSectorID(); ?>
						<a class="move_hack" href="<?php echo SmrSession::getNewHREF($UniGen); ?>"></a><?php
					}
					else if($GalaxyMap) { ?>
						<a class="move_hack" href="<?php echo $Sector->getGalaxyMapHREF(); ?>"></a><?php
					}
					else if($isLinkedSector) { ?>
						<a class="move_hack" href="<?php echo $Sector->getLocalMapHREF(); ?>"></a><?php
					}
					else if($isCurrentSector) { ?>
						<a class="move_hack" href="<?php echo Globals::getCurrentSectorHREF(); ?>"></a><?php
					} ?>
				</td><?php
			} unset($Sector); ?>
		</tr><?php
		//A little hacky but clearing these caches for each row of gal map drastically reduces total memory usage, and these caches should never be needed after this point either.
		SmrPort::clearCache();
		SmrForce::clearCache();
		SmrPlanet::clearCache();
	} unset($MapSector); ?>
</table>