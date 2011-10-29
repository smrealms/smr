<div id="sector_port" class="ajax">
	<?php if($ThisSector->hasPort())
	{
		$Port =& $ThisSector->getPort(); ?>
		<table class="standard csl">
			<tr>
				<th colspan="2">Port</th>
				<th>Option</th>
			</tr>
			<tr>
				<td style="border-right:none">
					<a href="<?php echo Globals::getTraderRelationsHREF(); ?>"><?php echo $ThisPlayer->getColouredRaceName($Port->getRaceID()); ?></a> Port <?php echo $Port->getSectorID(); ?> (Level <?php echo $Port->getLevel(); ?>)<br />
						<div class="goods">
							<img src="images/port/buy.gif" alt="Buy" title="Buy" /><?php
							foreach($Port->getVisibleGoodsSold($ThisPlayer) as $Good)
							{
								?><img src="<?php echo $Good['ImageLink']; ?>" title="<?php echo $Good['Name']; ?>" alt="<?php echo $Good['Name']; ?>" /><?php
							}
							?><br /><img src="images/port/sell.gif" alt="Sell" title="Sell" /><?php
							foreach($Port->getVisibleGoodsBought($ThisPlayer) as $Good)
							{
								?><img src="<?php echo $Good['ImageLink']; ?>" title="<?php echo $Good['Name']; ?>" alt="<?php echo $Good['Name']; ?>" /><?php
							} ?>
						</div>
					</td>
				<td style="padding-right:1px;border-left:none;vertical-align:bottom;text-align:right">
					<img style="height:<?php echo $Port->getUpgradePercent()*32; ?>px;width:6px;border:2px solid #000000;border-bottom:none;" src="images/green.gif" alt="Upgrade" title="Upgrade" /><img style="height:<?php echo $Port->getCreditsPercent()*32; ?>px;width:6px;border:2px solid #000000;border-bottom:none;" src="images/blue.gif" alt="Credits" title="Credits" /><img style="height:<?php echo $Port->getReinforcePercent()*32; ?>px;width:6px;border:2px solid #000000;border-bottom:none;" src="images/red.gif" alt="Defense" title="Defense" />
				</td>
				<td class="center shrink noWrap"><?php
					if($Port->isUnderAttack())
					{
						?><span class="red bold">ALERT!!</span><?php
					}
					else if($PortIsAtWar)
					{
						?><span class="red bold">WAR!!</span><?php
					}
					else
					{ ?>
						<div class="buttonA">
							<a class="buttonA" href="<?php echo $Port->getTradeHREF() ?>">&nbsp;Trade&nbsp;</a>
						</div><?php
					} ?>&nbsp;
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $Port->getRaidWarningHREF() ?>">&nbsp;Raid&nbsp;</a>
					</div>
				</td>
			</tr>
		</table><br /><?php
	} ?>
</div>