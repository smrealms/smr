<?php declare(strict_types=1);

use Smr\Globals;

/**
 * @var Smr\Player $ThisPlayer
 * @var Smr\Sector $ThisSector
 * @var Smr\Ship $ThisShip
 * @var array<array<Smr\Sector>> $MapSectors
 * @var ?string $DragLocationHREF
 * @var ?string $DragPlanetHREF
 * @var ?string $DragPortHREF
 * @var ?string $DragWarpHREF
 * @var ?string $ModifySectorHREF
 */

?>
<table class="lmt centered"><?php
	$GalaxyMap = isset($GalaxyMap) && $GalaxyMap;
	$UniGen ??= false;
	$MapPlayer = $UniGen ? null : $ThisPlayer;
	$MovementTypes = ['Up', 'Left', 'Right', 'Down'];
	foreach ($MapSectors as $MapSector) { ?>
		<tr><?php
			foreach ($MapSector as $Sector) {
				$isCurrentSector = !$UniGen && $ThisSector->equals($Sector);
				$isLinkedSector = !$UniGen && $ThisSector->isLinkedSector($Sector);
				$isSeedlistSector = isset($ShowSeedlistSectors) && $ShowSeedlistSectors && $MapPlayer?->getAlliance()->isInSeedlist($Sector) === true;
				$isVisited = $Sector->isVisited($MapPlayer); ?>
				<td id="sector<?php echo $Sector->getSectorID(); ?>" class="ajax">
					<div class="lm_sector galaxy<?php echo $Sector->getGalaxyID();
						if ($isSeedlistSector) {
							if ($isCurrentSector) { ?> currentSeclm_seedlist<?php } else { ?> lm_seedlist<?php }
						}
						if ($isCurrentSector) { ?> currentSeclm<?php
						} elseif ($isLinkedSector && !$isVisited) { ?> connectSeclmu<?php
						} elseif ($isLinkedSector) { ?> connectSeclm<?php
						} elseif ($isVisited) { ?> normalSeclm<?php
						} else { ?> normalSeclmu<?php } ?>"><?php

						if ($isVisited) {
							foreach ($MovementTypes as $MovementType) { ?>
								<div class="lm<?php echo $MovementType; ?> <?php echo $Sector->hasLink($MovementType) ? 'con' : 'wall'; ?>"><?php
									if (isset($ToggleLinkHREF)) { ?>
										<div
											class="toggle_link"
											onclick="toggleLink(this)"
											data-href="<?php echo $ToggleLinkHREF; ?>"
											data-sector="<?php echo $Sector->getSectorID(); ?>"
											data-dir="<?php echo $MovementType; ?>"
										></div><?php
									} ?>
								</div><?php
							}
							if ($Sector->hasLocation() || $Sector->hasPlanet()) { ?>
								<div class="lmlocs"><?php
									foreach ($Sector->getLocations() as $Location) {
										if ($isCurrentSector && $Location->hasAction() && !$GalaxyMap) {
											?><a href="<?php echo $Location->getExamineHREF() ?>"><?php
										} ?>
										<img src="<?php echo $Location->getImage() ?>" width="16" height="16" alt="<?php echo $Location->getName() ?>" title="<?php echo $Location->getName() ?>" <?php
											if ($UniGen) { ?>
												class="drag_loc"
												data-href="<?php echo $DragLocationHREF; ?>"
												data-sector="<?php echo $Sector->getSectorID(); ?>"
												data-loc="<?php echo $Location->getTypeID(); ?>" <?php
											} ?>
										/><?php
										if ($isCurrentSector && $Location->hasAction() && !$GalaxyMap) { ?></a><?php }
									}
									if ($Sector->hasPlanet()) {
										$planet = $Sector->getPlanet();
										if ($isCurrentSector && !$GalaxyMap) {
											?><a href="<?php echo $planet->getExamineHREF(); ?>"><?php
										} ?>
										<img title="<?php echo $planet->getTypeName() ?>" alt="Planet" src="<?php echo $planet->getTypeImage() ?>" width="16" height="16" <?php
											if ($UniGen) { ?>
												class="drag_loc"
												data-href="<?php echo $DragPlanetHREF; ?>"
												data-sector="<?php echo $Sector->getSectorID(); ?>" <?php
											} ?>
										/><?php
										if ($isCurrentSector && !$GalaxyMap) { ?></a><?php }
									} ?>
								</div><?php
							}
							$Port = null;
							if (($UniGen || $isCurrentSector) && $Sector->hasPort()) {
								$Port = $Sector->getPort();
							} elseif ($Sector->hasCachedPort($MapPlayer)) {
								$Port = $Sector->getCachedPort($MapPlayer);
							}
							if ($Port !== null) { ?>
								<div class="lmport <?php if ($Sector->getLinkLeft() !== 0) { ?>a<?php } else { ?>b<?php } ?>
									"><?php
									if ($UniGen) { ?>
										<div
											class="drag_loc"
											data-href="<?php echo $DragPortHREF; ?>"
											data-sector="<?php echo $Sector->getSectorID(); ?>"
										><?php
									}
									if ($isCurrentSector && !$GalaxyMap) {
										?><a href="<?php echo Globals::getTradeHREF(); ?>"><?php
									} ?>
									<img src="images/port/sell.png" width="5" height="16" alt="Sell (<?php echo $Port->getRaceName(); ?>)"
										title="Sell (<?php echo $Port->getRaceName(); ?>)" class="port<?php echo $Port->getRaceID(); ?>"/><?php
									foreach ($Port->getVisibleGoodsBought($MapPlayer) as $Good) {
										echo $Good->getImageHTML();
									} ?>
									<br />
									<img src="images/port/buy.png" width="5" height="16" alt="Buy (<?php echo $Port->getRaceName(); ?>)"
										title="Buy (<?php echo $Port->getRaceName(); ?>)" class="port<?php echo $Port->getRaceID(); ?>"/><?php
									foreach ($Port->getVisibleGoodsSold($MapPlayer) as $Good) {
										echo $Good->getImageHTML();
									}
									if ($UniGen) { ?></div><?php }
									if ($isCurrentSector && !$GalaxyMap) { ?></a><?php } ?>
								</div><?php
							}
						}
						if (($isVisited && $Sector->hasWarp()) || ($MapPlayer?->isPartOfCourse($Sector) === true)) { ?>
							<div class="lmp"><?php
								if ($MapPlayer?->isPartOfCourse($Sector) === true) {
									?><img title="Course" alt="Course" src="images/plot_icon.gif" width="16" height="16"/><?php
								}
								if ($isVisited) {
									if ($Sector->hasWarp()) {
										if ($GalaxyMap) { ?><a href="<?php echo $Sector->getWarpSector()->getGalaxyMapHREF(); ?>"><?php } elseif ($isCurrentSector) { ?><a href="<?php echo $Sector->getWarpSector()->getLocalMapMoveHREF($ThisPlayer); ?>"><?php } ?>
											<img src="images/warp.png" width="16" height="16"
												title="Warp to #<?php echo $Sector->getWarp(); ?> (<?php echo $Sector->getWarpSector()->getGalaxy()->getDisplayName(); ?>)"
												alt="Warp to #<?php echo $Sector->getWarp(); ?>" <?php
												if ($UniGen) { ?>
													class="drag_loc"
													data-href="<?php echo $DragWarpHREF; ?>"
													data-sector="<?php echo $Sector->getSectorID(); ?>" <?php
												} ?>
											/><?php
										if ($isCurrentSector || $GalaxyMap) { ?></a><?php }
									}
								} ?>
							</div><?php
						}
						if ($MapPlayer !== null) { // skip in UniGen
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
						if ($UniGen) { ?>
							<form action="<?php echo $ModifySectorHREF; ?>" method="POST">
								<button class="move_hack" name="sector_edit" value="<?php echo $Sector->getSectorID(); ?>"></button>
							</form><?php
						} elseif ($GalaxyMap) { ?>
							<a class="move_hack" href="<?php echo $Sector->getGalaxyMapHREF(); ?>"></a><?php
						} elseif ($isLinkedSector) { ?>
							<a class="move_hack" href="<?php echo $Sector->getLocalMapMoveHREF($ThisPlayer); ?>"></a><?php
						} elseif ($isCurrentSector) { ?>
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
