{if $ThisSector->hasPort()}
	{assign var=Port value=$ThisSector->getPort()}
	<table cellspacing="0" cellpadding="0" class="standard csl">
		<tr>
			<th colspan="2">Port</th>
			<th>Option</th>
		</tr>
		<tr>
			<td style="border-right:none">
				<a href="{$TraderRelationsLink}">{$PortRaceName}</a> Port {$Port->getSectorID()} (Level {$Port->getLevel()})<br />
					<div class="goods">
						<img src="images/port/buy.gif" alt="Buy" title="Buy" />{foreach from=$Port->getVisibleGoodsSold($ThisPlayer) item=Good}<img src="{$Good.ImageLink}" title="{$Good.Name}" alt="{$Good.Name}" />{/foreach}
						<br /><img src="images/port/sell.gif" alt="Sell" title="Sell" />{foreach from=$Port->getVisibleGoodsBought($ThisPlayer) item=Good}<img src="{$Good.ImageLink}" title="{$Good.Name}" alt="{$Good.Name}" />{/foreach}
					</div>
				</td>
			<td style="padding-right:1px;border-left:none;vertical-align:bottom;text-align:right">
				<img style="height:{$Port->getUpgradePercent() * 32}px;width:6px;border:2px solid #000000;border-bottom:none;" src="images/green.gif" alt="Upgrade" title="Upgrade" /><img style="height:{$Port->getCreditsPercent() * 32}px;width:6px;border:2px solid #000000;border-bottom:none;" src="images/blue.gif" alt="Credits" title="Credits" /><img style="height:{$Port->getReinforcePercent() * 32}px;width:6px;border:2px solid #000000;border-bottom:none;" src="images/red.gif" alt="Defense" title="Defense" />
			</td>
			<td class="center shrink nowrap">
				<div class="buttonA">
					{if $Port->isUnderAttack()}
						<span class="red bold">ALERT!!</span>
					{elseif $PortIsAtWar}
						<span class="red bold">WAR!!</span>
					{else}
						<a class="buttonA" href="{$Port->getTradeHREF()}">&nbsp;Trade&nbsp;</a>
					{/if}
				</div>
				<div class="buttonA">
					<a class="buttonA" href="{$Port->getRaidWarningHREF()}">&nbsp;Raid&nbsp;</a>
				</div>
			</td>
		</tr>
	</table><br />
{/if}