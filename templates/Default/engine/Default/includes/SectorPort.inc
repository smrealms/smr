<div id="sector_port" class="ajax">
	<?php if($ThisSector->hasPort()) {
		$Port = $ThisSector->getPort(); ?>
		<table class="standard csl">
			<tr>
				<th colspan="2">Port</th>
				<th>Option</th>
			</tr>
			<tr>
				<td style="border-right:none">
					<a href="<?php echo Globals::getTraderRelationsHREF(); ?>"><?php echo $ThisPlayer->getColouredRaceName($Port->getRaceID()); ?></a> Port <?php echo $Port->getSectorID(); ?> (Level <?php echo $Port->getLevel(); ?>)<br />
						<div class="goods">
							<img src="images/port/buy.png" width="5" height="16" alt="Buy" 
								title="Buy" class="port<?php echo $Port->getRaceID(); ?>"/><?php
							foreach ($Port->getVisibleGoodsSold($ThisPlayer) as $GoodID) {
								$Good =& Globals::getGood($GoodID);
								?><img src="<?php echo $Good['ImageLink']; ?>" width="13" height="16" title="<?php echo $Good['Name']; ?>" alt="<?php echo $Good['Name']; ?>" /><?php
							}
							?><br /><img src="images/port/sell.png" width="5" height="16" alt="Sell" 
								title="Sell" class="port<?php echo $Port->getRaceID(); ?>"/><?php
							foreach ($Port->getVisibleGoodsBought($ThisPlayer) as $GoodID) {
								$Good =& Globals::getGood($GoodID);
								?><img src="<?php echo $Good['ImageLink']; ?>" width="13" height="16" title="<?php echo $Good['Name']; ?>" alt="<?php echo $Good['Name']; ?>" /><?php
							} ?>
						</div>
					</td>
				<td style="padding-right:1px;border-left:none;" class="right bottom">
					<img height="<?php echo $Port->getUpgradePercent()*32; ?>" width="6px" style="border:2px solid #000000;border-bottom:none;" src="images/green.gif" alt="Upgrade" title="Upgrade" /><img height="<?php echo $Port->getCreditsPercent()*32; ?>" width="6px" style="border:2px solid #000000;border-bottom:none;" src="images/blue.gif" alt="Credits" title="Credits" /><img height="<?php echo $Port->getReinforcePercent()*32; ?>" width="6px" style="border:2px solid #000000;border-bottom:none;" src="images/red.gif" alt="Defense" title="Defense" />
				</td>
				<td class="center shrink noWrap"><?php
					if($Port->isUnderAttack()) {
						?><span class="red bold">ALERT!!</span><?php
					}
					else if($PortIsAtWar) {
						?><span class="red bold">WAR!!</span><?php
					}
					else { ?>
						<div class="buttonA">
							<a class="buttonA" href="<?php echo Globals::getTradeHREF() ?>">Trade</a>
						</div><?php
					} ?>&nbsp;
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $Port->getRaidWarningHREF() ?>">Raid</a>
					</div>
				</td>
			</tr>
		</table><br /><?php
	} ?>
</div>
