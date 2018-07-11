<?php echo $Message; ?>

<p>What can I do for ya?</p>
<div class="buttonA">
	<a class="buttonA" href="<?php echo $GossipHREF; ?>">&nbsp;Talk to Bartender&nbsp;</a>
</div>
<br /><br />

<h2>Drinks</h2><br />
Wanna buy a drink? I got some good stuff! Just what you need after a hard day's hunting.<br /><br />

<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarBuyDrinkHREF(); ?>">&nbsp;Buy a drink ($10)&nbsp;</a>
</div>&nbsp;&nbsp;&nbsp;&nbsp;
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarBuyWaterHREF(); ?>">&nbsp;Buy some water ($10)&nbsp;</a>
</div>

<br />
<br />
<h2>Gambling</h2><br />
So you're not the drinking type huh? Well how about some good ole gambling?<br />
<br />

<?php
if ($WinningTicket) { ?>
	Congratulations. You have a winning lotto ticket.<br />
	<br />
	<div class="buttonA">
		<a class="buttonA" href="<?php echo Globals::getBarLottoClaimHREF(); ?>">&nbsp;Claim Your Prize (<?php echo number_format($WinningTicket); ?> Cr)&nbsp;</a>
	</div><br />
	<br /><?php
} ?>

<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarLottoPlayHREF(); ?>">&nbsp;Play the Galactic Lotto&nbsp;</a>
</div>
&nbsp;&nbsp;&nbsp;&nbsp;
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarBlackjackHREF(); ?>">&nbsp;Play Some Blackjack&nbsp;</a>
</div>

<br />
<br />

<h2>Ship</h2><br />
Well... of course you could always pay our painters to customise your ship name, or even spray on your favourite logo!<br />
<br />
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBuyShipnameHREF(); ?>">&nbsp;Customize Ship Name (<?php echo min(CREDITS_PER_TEXT_SHIP_NAME, CREDITS_PER_HTML_SHIP_NAME, CREDITS_PER_SHIP_LOGO); ?>-<?php echo max(CREDITS_PER_TEXT_SHIP_NAME, CREDITS_PER_HTML_SHIP_NAME, CREDITS_PER_SHIP_LOGO); ?> SMR Credits)&nbsp;</a>
</div>

<br />
<br />

<h2>Systems</h2><br />
We just got in a new system that can send information from your scout drones or recent news directly to your main screen! It only costs <?php echo CREDITS_PER_TICKER; ?> SMR credits for 5 days! Or you can buy a system to block these messages.<br />
<br />
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarTickerHREF(); ?>">&nbsp;Buy System (<?php echo CREDITS_PER_TICKER; ?> SMR Credits)&nbsp;</a>
</div>
<br />
<br />

<h2>Maps</h2><br />
New intelligence has just come in!  We now have full maps of EVERY galaxy!  We are willing to sell you the newest maps of each galaxy for <?php echo CREDITS_PER_GAL_MAP; ?> SMR credits each!<br />
<br />
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarGalMapHREF(); ?>">&nbsp;Buy a Galaxy Map (<?php echo CREDITS_PER_GAL_MAP; ?> SMR Credits)&nbsp;</a>
</div><br /><br />
