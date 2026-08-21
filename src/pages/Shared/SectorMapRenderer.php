<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Globals;
use Smr\Player;
use Smr\Sector;

class SectorMapRenderer {

	/**
	 * @param array<array<\Smr\Sector>> $MapSectors
	 * @param ?array{ModifySector: string, ToggleLink: string, DragLocation: string, DragPlanet: string, DragPort: string, DragWarp: string} $EditLinks
	 */
	public static function render(
		?Player $ThisPlayer,
		array $MapSectors,
		bool $GalaxyMap,
		bool $HideAlliedForces,
		bool $ShowSeedlistSectors,
		?array $EditLinks = null,
	): void {
		?>
		<table class="lmt centered"><?php
			$MapPlayer = $EditLinks === null ? $ThisPlayer : null;
			$MovementTypes = Sector::getLinkDirs();
			foreach ($MapSectors as $MapSector) { ?>
				<tr><?php
					foreach ($MapSector as $Sector) {
						$isCurrentSector = $MapPlayer?->getSector()->equals($Sector) ?? false;
						$isLinkedSector = $MapPlayer?->getSector()->isLinkedSector($Sector) ?? false;
						$isSeedlistSector = $ShowSeedlistSectors && $MapPlayer?->getAlliance()->isInSeedlist($Sector) === true;
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
											if ($EditLinks !== null) { ?>
												<div
													class="toggle_link"
													onclick="toggleLink(this)"
													data-href="<?php echo $EditLinks['ToggleLink']; ?>"
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
													if ($EditLinks !== null) { ?>
														class="drag_loc"
														data-href="<?php echo $EditLinks['DragLocation']; ?>"
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
													if ($EditLinks !== null) { ?>
														class="drag_loc"
														data-href="<?php echo $EditLinks['DragPlanet']; ?>"
														data-sector="<?php echo $Sector->getSectorID(); ?>" <?php
													} ?>
												/><?php
												if ($isCurrentSector && !$GalaxyMap) { ?></a><?php }
											} ?>
										</div><?php
									}
									$Port = null;
									if (($MapPlayer === null || $isCurrentSector) && $Sector->hasPort()) {
										$Port = $Sector->getPort();
									} elseif ($Sector->hasCachedPort($MapPlayer)) {
										$Port = $Sector->getCachedPort($MapPlayer);
									}
									if ($Port !== null) { ?>
										<div class="lmport <?php if ($Sector->getLinkLeft() !== 0) { ?>a<?php } else { ?>b<?php } ?>
											"><?php
											if ($EditLinks !== null) { ?>
												<div
													class="drag_loc"
													data-href="<?php echo $EditLinks['DragPort']; ?>"
													data-sector="<?php echo $Sector->getSectorID(); ?>"
												><?php
											}
											if ($isCurrentSector && !$GalaxyMap) {
												?><a href="<?php echo Globals::getTradeHREF(); ?>"><?php
											} ?>
											<img src="images/port/buy.png" width="5" height="16" alt="Buy (<?php echo $Port->getRaceName(); ?>)"
												title="Buy (<?php echo $Port->getRaceName(); ?>)" class="port<?php echo $Port->getRaceID(); ?>"/><?php
											foreach ($Port->getVisibleGoodsPlayerBuys($MapPlayer) as $Good) {
												echo $Good->getImageHTML();
											} ?>
											<br />
											<img src="images/port/sell.png" width="5" height="16" alt="Sell (<?php echo $Port->getRaceName(); ?>)"
												title="Sell (<?php echo $Port->getRaceName(); ?>)" class="port<?php echo $Port->getRaceID(); ?>"/><?php
											foreach ($Port->getVisibleGoodsPlayerSells($MapPlayer) as $Good) {
												echo $Good->getImageHTML();
											}
											if ($EditLinks !== null) { ?></div><?php }
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
												if ($GalaxyMap) { ?><a href="<?php echo $Sector->getWarpSector()->getGalaxyMapHREF(); ?>"><?php } elseif ($isCurrentSector) { ?><a href="<?php echo $Sector->getWarpSector()->getLocalMapMoveHREF($MapPlayer); ?>"><?php } ?>
													<img src="images/warp.png" width="16" height="16"
														title="Warp to #<?php echo $Sector->getWarp(); ?> (<?php echo $Sector->getWarpSector()->getGalaxy()->getDisplayName(); ?>)"
														alt="Warp to #<?php echo $Sector->getWarp(); ?>" <?php
														if ($EditLinks !== null) { ?>
															class="drag_loc"
															data-href="<?php echo $EditLinks['DragWarp']; ?>"
															data-sector="<?php echo $Sector->getSectorID(); ?>" <?php
														} ?>
													/><?php
												if ($isCurrentSector || $GalaxyMap) { ?></a><?php }
											}
										} ?>
									</div><?php
								}
								if ($MapPlayer !== null) { // skip in UniGen
									$CanScanSector = ($MapPlayer->getShip()->hasScanner() && $isLinkedSector) || $isCurrentSector;
									$ShowFriendlyForces = (
										$HideAlliedForces ?
										$Sector->hasPlayerForces($MapPlayer) :
										$Sector->hasFriendlyForces($MapPlayer)
									);
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
								if ($EditLinks !== null) { ?>
									<form action="<?php echo $EditLinks['ModifySector']; ?>" method="POST">
										<button class="move_hack" name="sector_edit" value="<?php echo $Sector->getSectorID(); ?>"></button>
									</form><?php
								} elseif ($GalaxyMap) { ?>
									<a class="move_hack" href="<?php echo $Sector->getGalaxyMapHREF(); ?>"></a><?php
								} elseif ($isLinkedSector) { ?>
									<a class="move_hack" href="<?php echo $Sector->getLocalMapMoveHREF($MapPlayer); ?>"></a><?php
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

		<?php
	}

}
