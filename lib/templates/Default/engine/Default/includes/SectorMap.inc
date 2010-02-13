<table class="lmt" style="margin: auto;"><?php
	$GalaxyMap = isset($GalaxyMap)&&$GalaxyMap;
	foreach($MapSectors as &$MapSector)
	{ ?>
		<tr><?php
			foreach($MapSector as &$Sector)
			{
				$isCurrentSector = $ThisSector->equals($Sector);
				$isLinkedSector = $ThisSector->isLinked($Sector); ?>
				<td>
					<div id="sector<?php echo $Sector->getSectorID(); ?>" class="ajax <?php if($isCurrentSector){ ?>currentSeclm<?php }else if($isLinkedSector){ ?>connectSeclm<?php }else if($Sector->isVisited($ThisPlayer)){ ?>normalSeclm<?php }else{ ?>normalSeclmu<?php } ?> lm_sector">
						<div class="lmup"><?php
							if($Sector->isVisited($ThisPlayer))
							{ ?>
								<div class="center lmup"><?php
									if($Sector->getLinkUp())
									{ ?>
										<img src="images/link_hor.gif" alt="Up" title="Up" /><?php
									} ?>
								</div><?php
							}
							if(($Sector->isVisited($ThisPlayer) && ($Sector->hasWarp() || $Sector->hasMine())) || $ThisPlayer->isPartOfCourse($Sector) || ($ThisPlayer->hasPlottedCourse()&&$isCurrentSector))
							{ ?>
								<div class="lmp"><?php
									if($ThisPlayer->isPartOfCourse($Sector) || ($ThisPlayer->hasPlottedCourse()&&$isCurrentSector))
									{
										?><img title="Course" alt="Course" src="images/plot_icon.gif"/><?php
									}
									if($Sector->isVisited($ThisPlayer) && $Sector->hasWarp())
									{
										if($isCurrentSector && !$GalaxyMap)
										{
											$WarpSector =& $Sector->getWarpSector(); ?>
											<a href="<?php echo $WarpSector->getLocalMapHREF(); ?>"><?php
										} ?>
											<img title="Warp to #<?php echo $Sector->getWarp(); ?>" alt="Warp to #<?php echo $Sector->getWarp(); ?>" src="images/warp.gif" /><?php
										if($isCurrentSector && !$GalaxyMap){ ?></a><?php }
									}
									if($Sector->isVisited($ThisPlayer) && $Sector->hasMine())
									{
										?><img src="images/asteroid.gif" alt="Mining Available Here" title="Mining Available Here" /><?php
									} ?>
								</div><?php
							}
							if((($ThisShip->hasScanner() && $isLinkedSector) || $isCurrentSector) && ($Sector->hasForces() || $Sector->hasTraders()))
							{ ?>
								<div class="lmtf"><?php
									if(($isCurrentSector && $Sector->hasOtherTraders($ThisPlayer)) || ($isLinkedSector && $Sector->hasTraders()))
									{
										if($Sector->hasEnemyTraders($ThisPlayer))
										{
											?><img title="Enemy Trader" alt="Enemy Trader" src="images/enemyTrader.png"/><?php
										}
										if($Sector->hasFriendlyTraders($ThisPlayer))
										{
											?><img title="Friendly Trader" alt="Friendly Trader" src="images/friendlyTrader.png"/><?php
										}
									}
									if($Sector->hasForces())
									{
										if($Sector->hasEnemyForces($ThisPlayer))
										{
											?><img title="Enemy Forces" alt="Enemy Forces" src="images/enemyForces.png"/><?php
										}
										if($Sector->hasFriendlyForces($ThisPlayer))
										{
											?><img title="Friendly Forces" alt="Friendly Forces" src="images/friendlyForces.png"/><?php
										}
										/*?><img title="Forces" alt="Forces" src="images/forces.jpg"/><?php*/
									} ?>
								</div><?php
							} ?>
						</div><?php
						if($Sector->isVisited($ThisPlayer))
						{
							if($Sector->hasLocation() || $Sector->hasPlanet())
							{ ?>
								<div class="center lmloc fullwidth"><?php
								if($Sector->hasLocation())
								{
									foreach($Sector->getLocations() as $Location)
									{
										if($isCurrentSector && $Location->hasAction() && !$GalaxyMap)
										{
											?><a href="<?php echo $Location->getExamineHREF() ?>"><?php
										} ?>
											<img src="<?php echo $Location->getImage() ?>" alt="<?php echo $Location->getName() ?>" title="<?php echo $Location->getName() ?>" /><?php
										if($isCurrentSector && $Location->hasAction() && !$GalaxyMap){ ?></a><?php }
									}
								}
								if($Sector->hasPlanet())
								{
									if($isCurrentSector && !$GalaxyMap)
									{
										?><a href="<?php echo $Sector->getPlanet()->getExamineHREF(); ?>"><?php
									} ?>
									<img title="Planet" alt="Planet" src="images/planet.gif"/><?php
									if($isCurrentSector && !$GalaxyMap){ ?></a><?php }
								} ?>
								</div><?php
							} ?>
							<div class="left lmleft"><?php
								if($Sector->getLinkLeft())
								{
									?><img src="images/link_ver.gif" alt="Left" title="Left" /><?php
								} ?>
							</div><?php
						} ?>
						<div class="lmsector center <?php if($Sector->isVisited($ThisPlayer)){ ?>visited<?php }else{ ?>unvisited<?php } ?> fullwidth">
							<?php echo $Sector->getSectorID(); ?>
						</div><?php
						if($Sector->isVisited($ThisPlayer))
						{
							?><div class="right lmright"><?php
								if($Sector->getLinkRight())
								{
									?><img src="images/link_ver.gif" alt="Right" title="Right" /><?php
								} ?>
							</div><?php
							if(($isCurrentSector && $Sector->hasPort()) || $Sector->hasCachedPort($ThisPlayer))
							{
								if($isCurrentSector && $Sector->hasPort())
								{
									$Port =& $Sector->getPort();
								}
								else if($Sector->hasCachedPort($ThisPlayer))
								{
									$Port =& $Sector->getCachedPort($ThisPlayer);
								} ?>
								<div class="left lmport"><?php
									if($isCurrentSector && !$GalaxyMap)
									{
										?><a href="<?php echo $Port->getTradeHREF(); ?>"><?php
									} ?>
										<img src="images/port/buy.gif" alt="Buy (<?php echo $Port->getRaceName(); ?>)" title="Buy (<?php echo $Port->getRaceName(); ?>)" /><?php
											foreach($Port->getVisibleGoodsSold($ThisPlayer) as $Good)
											{ ?>
												<img src="<?php echo $Good['ImageLink']; ?>" title="<?php echo $Good['Name'] ?>" alt="<?php echo $Good['Name']; ?>" /><?php
											} ?><br />
										<img src="images/port/sell.gif" alt="Sell (<?php echo $Port->getRaceName(); ?>)" title="Sell (<?php echo $Port->getRaceName(); ?>)" /><?php
											foreach($Port->getVisibleGoodsBought($ThisPlayer) as $Good)
											{ ?>
												<img src="<?php echo $Good['ImageLink']; ?>" title="<?php echo $Good['Name'] ?>" alt="<?php echo $Good['Name']; ?>" /><?php
											}
									if($isCurrentSector && !$GalaxyMap){ ?></a><?php } ?>
								</div><?php
								} ?>
							<div class="center lmdown fullwidth"><?php
								if($Sector->getLinkDown()){ ?>
									<img src="images/link_hor.gif" alt="Down" title="Down" /><?php
								} ?>
							</div>
						<?php
						}
						if($GalaxyMap)
						{ ?>
							<a href="<?php echo $Sector->getGalaxyMapHREF(); ?>"><img title="" alt="" class="move_hack" src="images/blank.gif"/></a><?php
						}
						else if($isLinkedSector)
						{ ?>
							<a href="<?php echo $Sector->getLocalMapHREF(); ?>"><img title="" alt="" class="move_hack" src="images/blank.gif"/></a><?php
						}
						else if($isCurrentSector)
						{ ?>
							<a href="<?php echo Globals::getCurrentSectorHREF(); ?>"><img title="" alt="" class="move_hack" src="images/blank.gif"/></a><?php
						} ?>
					</div>
				</td><?php
			} unset($Sector); ?>
		</tr><?php
		} unset($MapSector); ?>
</table>