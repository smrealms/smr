<table class="lmt">
	{foreach from=$MapSectors item=MapSector}
		<tr>
			{foreach from=$MapSector item=Sector}
				{assign var=isCurrentSector value=$ThisSector->equals($Sector)}
				{assign var=isLinkedSector value=$ThisSector->isLinked($Sector)}
				<td>
					<div class="{if $isCurrentSector}currentSeclm{elseif $isLinkedSector}connectSeclm{elseif $Sector->isVisited()}normalSeclm{else}normalSeclmu{/if} lm_sector">
						<div class="lmup">
							{if $Sector->isVisited()}
								<div class="center lmup">
									{if $Sector->getLinkUp()}
										<img src="images/link_hor.gif" alt="Up" title="Up" />
									{/if}
								</div>
								{if $Sector->hasWarp() || $Sector->hasMine() || $ThisPlayer->isPartOfCourse($Sector)}
									<div class="lmp">
										{if $ThisPlayer->isPartOfCourse($Sector)}
											<img title="Course" alt="Course" src="images/plot_icon.gif"/>
										{/if}
										{if $Sector->hasWarp()}
											{if $isCurrentSector}{assign var=WarpSector value=$Sector->getLinkWarpSector()}<a href="{$WarpSector->getLocalMapHREF()}">{/if}
												<img title="Warp to #{$Sector->getLinkWarp()}" alt="Warp to #{$Sector->getLinkWarp()}" src="images/warp.gif" />
											{if $isCurrentSector}</a>{/if}
										{/if}
										{if $Sector->hasMine()}
											<img src="images/asteroid.gif" alt="Mining Available Here" title="Mining Available Here" />
										{/if}
									</div>
								{/if}
							{/if}
							{if (($ThisShip->hasScanner() && $isLinkedSector) || $isCurrentSector) && ($Sector->hasForces() || $Sector->hasTraders())}
								<div class="lmtf">
									{if ($isCurrentSector && $Sector->hasOtherTraders()) || ($isLinkedSector && $Sector->hasTraders())}
										<img title="Trader" alt="Trader" src="images/trader.jpg"/>
									{/if}
									{if $Sector->hasForces()}
										<img title="Forces" alt="Forces" src="images/forces.jpg"/>
									{/if}
								</div>
							{/if}
						</div>
						{if $Sector->isVisited()}
							{if $Sector->hasLocation() || $Sector->hasPlanet()}
								<div class="center lmloc fullwidth">
									{if $Sector->hasLocation()}{foreach from=$Sector->getLocations() item=Location}{if $isCurrentSector && $Location->hasAction()}<a href="{$Location->getExamineHREF()}">{/if}<img src="{$Location->getImage()}" alt="{$Location->getName()}" title="{$Location->getName()}" />{if $isCurrentSector && $Location->hasAction()}</a>{/if}{/foreach}{/if}{*
									*}{if $Sector->hasPlanet()}{if $isCurrentSector}<a href="{$Sector->getPlanet()->getExamineHREF()}">{/if}<img title="Planet" alt="Planet" src="images/planet.gif"/>{if $isCurrentSector}</a>{/if}{/if}
								</div>
							{/if}
							<div class="left lmleft">
								{if $Sector->getLinkLeft()}
									<img src="images/link_ver.gif" alt="Left" title="Left" />
								{/if}
							</div>
						{/if}
						<div class="lmsector center {if $Sector->isVisited()}dgreen{else}yellow{/if} fullwidth">
							{$Sector->getSectorID()}
						</div>
						{if $Sector->isVisited()}
							<div class="right lmright">
								{if $Sector->getLinkRight()}
									<img src="images/link_ver.gif" alt="Right" title="Right" />
								{/if}
							</div>
							{if ($isCurrentSector && $Sector->hasPort()) || $Sector->hasCachedPort()}
								{if ($isCurrentSector && $Sector->hasPort())}
									{assign var=Port value=$Sector->getPort()}
								{elseif $Sector->hasCachedPort()}
									{assign var=Port value=$Sector->getCachedPort()}
								{/if}
								<div class="left lmport">
									{if $isCurrentSector}<a href="{$Port->getTradeHREF()}">{/if}
										<img src="images/port/buy.gif" alt="Buy" title="Buy" />{foreach from=$Port->getVisibleGoodsSold($ThisPlayer) item=SoldGood}<img src="{$SoldGood.ImageLink}" title="{$SoldGood.Name}" alt="{$SoldGood.Name}" />{/foreach}<br />
										<img src="images/port/sell.gif" alt="Sell" title="Sell" />{foreach from=$Port->getVisibleGoodsBought($ThisPlayer) item=BoughtGood}<img src="{$BoughtGood.ImageLink}" title="{$BoughtGood.Name}" alt="{$BoughtGood.Name}" />{/foreach}
									{if $isCurrentSector}</a>{/if}
								</div>
							{/if}
							<div class="center lmdown fullwidth">
								{if $Sector->getLinkDown()}
									<img src="images/link_hor.gif" alt="Down" title="Down" />
								{/if}
							</div>
						{/if}
						{if $isLinkedSector}
							<a href="{$Sector->getLocalMapHREF()}"><img title="" alt="" class="move_hack" src="images/blank.gif"/></a>
						{elseif $isCurrentSector}
							<a href="{$Globals->getCurrentSectorHREF()}"><img title="" alt="" class="move_hack" src="images/blank.gif"/></a>
						{/if}
					</div>
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>