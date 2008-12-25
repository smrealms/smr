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
								{if $Sector->hasMine()}
									<div class="lmp">
										<img src="images/asteroid.gif" alt="Mining Available Here" title="Mining Available Here" />
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
							{if $Sector->hasLocation()}
								<div class="center lmloc fullwidth">
									{foreach from=$Sector->getLocations() item=Location}{if $isCurrentSector && $Location->hasAction()}<a href="{$Location->getExamineHREF()}">{/if}<img src="{$Location->getImage()}" alt="{$Location->getName()}" title="{$Location->getName()}" />{if $isCurrentSector}</a>{/if}{/foreach}{*
									*}{if $Sector->hasPlanet()}{if $isCurrentSector}<a href="{$Sector->getPlanet()->getExamineHREF()}">{/if}<img title="Planet" alt="Planet" src="images/planet.gif"/>{if $isCurrentSector}</a>{/if}{/if}
								</div>
							{/if}
							<div class="left lmleft">
								{if $Sector->getLinkLeft()}
									<img src="images/link_ver.gif" alt="Left" title="Left" />
								{/if}
							</div>
						{/if}
						<div class="lmsector center {if $Sector->isVisited()}dgreen{else}yellow{/if} fullwidth">{$Sector->getSectorID()}</div>
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
							<a href="{$Sector->getCurrentSectorHREF()}"><img title="" alt="" class="move_hack" src="images/blank.gif"/></a>
						{/if}
					</div>
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>
{*				<td><div class="connectSeclm lm_sector"><div class="lmup"><div class="center lmup"><img src="images/link_hor.gif" alt="Up" title="Up" /></div><div class="left lmleft"><img src="images/link_ver.gif" alt="Left" title="Left" /></div><div class="lmsector center dgreen fullwidth">#480</div><div class="right lmright"></div><div class="left lmport"><img src="images/port/buy.gif" alt="Buy" title="Buy" /><img src="images/port/2.gif" title="Food" alt="Food" /><br /><img src="images/port/sell.gif" alt="Sell" title="Sell" /><img src="images/port/3.gif" title="Ore" alt="Ore" /><img src="images/port/18.gif" title="Copper" alt="Copper" /></div><div class="center lmdown fullwidth"><img src="images/link_hor.gif" alt="Down" title="Down" /></div><a href="main.php?action=218"><img src="images/blank.gif" class="move_hack" alt="" title="" /></a></td><td><div class="normalSeclm lm_sector"><div class="lmup"><div class="center lmup"><img src="images/link_hor.gif" alt="Up" title="Up" /></div><div class="left lmleft"></div><div class="lmsector center dgreen fullwidth">#461</div><div class="right lmright"><img src="images/link_ver.gif" alt="Right" title="Right" /></div><div class="center lmdown fullwidth"><img src="images/link_hor.gif" alt="Down" title="Down" /></div></td></tr><tr><td><div class="connectSeclm lm_sector"><div class="lmup"><div class="center lmup"><img src="images/link_hor.gif" alt="Up" title="Up" /></div><div class="left lmleft"><img src="images/link_ver.gif" alt="Left" title="Left" /></div><div class="lmsector center dgreen fullwidth">#499</div><div class="right lmright"><img src="images/link_ver.gif" alt="Right" title="Right" /></div><div class="left lmport"><img src="images/port/buy.gif" alt="Buy" title="Buy" /><img src="images/port/2.gif" title="Food" alt="Food" /><img src="images/port/18.gif" title="Copper" alt="Copper" /><br /><img src="images/port/sell.gif" alt="Sell" title="Sell" /><img src="images/port/1.gif" title="Wood" alt="Wood" /><img src="images/port/4.gif" title="Precious Metals" alt="Precious Metals" /></div><div class="center lmdown fullwidth"></div><a href="main.php?action=219"><img src="images/blank.gif" class="move_hack" alt="" title="" /></a></td>
		<td>
			<div class="currentSeclm lm_sector">
				<div class="lmup">
					<div class="center lmup">
						<img src="images/link_hor.gif" alt="Up" title="Up" />
					</div>
				<div class="left lmleft">
					<img src="images/link_ver.gif" alt="Left" title="Left" />
				</div>
				<div class="lmsector center dgreen fullwidth">#500</div>
				<div class="right lmright">
					<img src="images/link_ver.gif" alt="Right" title="Right" />
				</div>
				<div class="left lmport">
					<a href="main.php?action=107">
						<img src="images/port/buy.gif" alt="Buy" title="Buy" />
						<img src="images/port/1.gif" title="Wood" alt="Wood" />
						<img src="images/port/18.gif" title="Copper" alt="Copper" />
						<img src="images/port/4.gif" title="Precious Metals" alt="Precious Metals" /><br />
						<img src="images/port/sell.gif" alt="Sell" title="Sell" />
					</a>
				</div>
				<div class="center lmdown fullwidth"></div>
				<a href="main.php?action=108">
					<img src="images/blank.gif" class="move_hack" alt="" title="" />
				</a>
			</div>
		</td>
	</tr>
</table>
</div></div></div>

<tr><td><div class="normalSeclm lm_sector"><div class="lmup"><div class="center lmup"></div><div class="lmp"><img src="images/asteroid.gif" alt="Mining Available Here" title="Mining Available Here" /></div><div class="left lmleft"></div><div class="lmsector center dgreen fullwidth">#479</div><div class="right lmright"><img src="images/link_ver.gif" alt="Right" title="Right" /></div><div class="center lmdown fullwidth"><img src="images/link_hor.gif" alt="Down" title="Down" /></div></td><td><div class="connectSeclm lm_sector"><div class="lmup"><div class="center lmup"><img src="images/link_hor.gif" alt="Up" title="Up" /></div><div class="left lmleft"><img src="images/link_ver.gif" alt="Left" title="Left" /></div><div class="lmsector center dgreen fullwidth">#480</div><div class="right lmright"></div><div class="left lmport"><img src="images/port/buy.gif" alt="Buy" title="Buy" /><img src="images/port/2.gif" title="Food" alt="Food" /><br /><img src="images/port/sell.gif" alt="Sell" title="Sell" /><img src="images/port/3.gif" title="Ore" alt="Ore" /><img src="images/port/18.gif" title="Copper" alt="Copper" /></div><div class="center lmdown fullwidth"><img src="images/link_hor.gif" alt="Down" title="Down" /></div><a href="main.php?action=218"><img src="images/blank.gif" class="move_hack" alt="" title="" /></a></td><td><div class="normalSeclm lm_sector"><div class="lmup"><div class="center lmup"><img src="images/link_hor.gif" alt="Up" title="Up" /></div><div class="left lmleft"></div><div class="lmsector center dgreen fullwidth">#461</div><div class="right lmright"><img src="images/link_ver.gif" alt="Right" title="Right" /></div><div class="center lmdown fullwidth"><img src="images/link_hor.gif" alt="Down" title="Down" /></div></td></tr><tr><td><div class="connectSeclm lm_sector"><div class="lmup"><div class="center lmup"><img src="images/link_hor.gif" alt="Up" title="Up" /></div><div class="left lmleft"><img src="images/link_ver.gif" alt="Left" title="Left" /></div><div class="lmsector center dgreen fullwidth">#499</div><div class="right lmright"><img src="images/link_ver.gif" alt="Right" title="Right" /></div><div class="left lmport"><img src="images/port/buy.gif" alt="Buy" title="Buy" /><img src="images/port/2.gif" title="Food" alt="Food" /><img src="images/port/18.gif" title="Copper" alt="Copper" /><br /><img src="images/port/sell.gif" alt="Sell" title="Sell" /><img src="images/port/1.gif" title="Wood" alt="Wood" /><img src="images/port/4.gif" title="Precious Metals" alt="Precious Metals" /></div><div class="center lmdown fullwidth"></div><a href="main.php?action=219"><img src="images/blank.gif" class="move_hack" alt="" title="" /></a></td><td><div class="currentSeclm lm_sector"><div class="lmup"><div class="center lmup"><img src="images/link_hor.gif" alt="Up" title="Up" /></div><div class="left lmleft"><img src="images/link_ver.gif" alt="Left" title="Left" /></div><div class="lmsector center dgreen fullwidth">#500</div><div class="right lmright"><img src="images/link_ver.gif" alt="Right" title="Right" /></div><div class="left lmport"><a href="main.php?action=220"><img src="images/port/buy.gif" alt="Buy" title="Buy" /><img src="images/port/1.gif" title="Wood" alt="Wood" /><img src="images/port/18.gif" title="Copper" alt="Copper" /><img src="images/port/4.gif" title="Precious Metals" alt="Precious Metals" /><br /><img src="images/port/sell.gif" alt="Sell" title="Sell" /></a></div><div class="center lmdown fullwidth"></div><a href="main.php?action=221"><img src="images/blank.gif" class="move_hack" alt="" title="" /></a></td><td><div class="connectSeclm lm_sector"><div class="lmup"><div class="center lmup"><img src="images/link_hor.gif" alt="Up" title="Up" /></div><div class="left lmleft"><img src="images/link_ver.gif" alt="Left" title="Left" /></div><div class="lmsector center dgreen fullwidth">#481</div><div class="right lmright"><img src="images/link_ver.gif" alt="Right" title="Right" /></div><div class="center lmdown fullwidth"><img src="images/link_hor.gif" alt="Down" title="Down" /></div><a href="main.php?action=222"><img src="images/blank.gif" class="move_hack" alt="" title="" /></a></td></tr><tr><td><div class="normalSeclm lm_sector"><div class="lmup"><div class="center lmup"></div>
<div class="center lmloc fullwidth"><img src="images/hardware.gif" alt="Crone Dronfusion" title="Crone Dronfusion" /></div>
<div class="left lmleft"><img src="images/link_ver.gif" alt="Left" title="Left" /></div><div class="lmsector center dgreen fullwidth">#19</div><div class="right lmright"></div><div class="center lmdown fullwidth"><img src="images/link_hor.gif" alt="Down" title="Down" /></div></td><td><div class="normalSeclm lm_sector"><div class="lmup"><div class="center lmup"></div><div class="left lmleft"></div><div class="lmsector center dgreen fullwidth">#20</div><div class="right lmright"><img src="images/link_ver.gif" alt="Right" title="Right" /></div><div class="left lmport"><img src="images/port/buy.gif" alt="Buy" title="Buy" /><img src="images/port/18.gif" title="Copper" alt="Copper" /><img src="images/port/4.gif" title="Precious Metals" alt="Precious Metals" /><img src="images/port/9.gif" title="Weapons" alt="Weapons" /><br /><img src="images/port/sell.gif" alt="Sell" title="Sell" /><img src="images/port/1.gif" title="Wood" alt="Wood" /><img src="images/port/3.gif" title="Ore" alt="Ore" /></div><div class="center lmdown fullwidth"><img src="images/link_hor.gif" alt="Down" title="Down" /></div></td><td><div class="normalSeclm lm_sector"><div class="lmup"><div class="center lmup"><img src="images/link_hor.gif" alt="Up" title="Up" /></div><div class="center lmloc fullwidth"><img src="images/bank.gif" alt="First Galactic Bank" title="First Galactic Bank" /><img src="images/government.gif" alt="Federal Headquarters" title="Federal Headquarters" /><img src="images/beacon.gif" alt="Federal Beacon" title="Federal Beacon" /><img src="images/bank.gif" alt="Federal Mint" title="Federal Mint" /></div><div class="left lmleft"><img src="images/link_ver.gif" alt="Left" title="Left" /></div><div class="lmsector center dgreen fullwidth">#1</div><div class="right lmright"><img src="images/link_ver.gif" alt="Right" title="Right" /></div><div class="center lmdown fullwidth"><img src="images/link_hor.gif" alt="Down" title="Down" /></div></td></tr></table></div></div></div></div>

*}