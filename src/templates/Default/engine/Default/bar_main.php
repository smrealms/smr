<?php echo $Message; ?>

<p>What can I do for ya?</p>
<div class="buttonA">
	<a class="buttonA" href="<?php echo $GossipHREF; ?>">Talk to Bartender</a>
</div>
<br /><br />

<h2>Drinks</h2><br />
Wanna buy a drink? I got some good stuff! Just what you need after a hard day's hunting.<br /><br />

<div class="buttonA">
	<a class="buttonA" href="<?php echo $BuyDrinkHREF; ?>">Buy a drink ($10)</a>
</div>&nbsp;&nbsp;&nbsp;&nbsp;
<div class="buttonA">
	<a class="buttonA" href="<?php echo $BuyWaterHREF; ?>">Buy some water ($10)</a>
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
		<a class="buttonA" href="<?php echo $LottoClaimHREF; ?>">Claim Your Prize (<?php echo number_format($WinningTicket); ?> Cr)</a>
	</div><br />
	<br /><?php
} ?>

<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarLottoPlayHREF(); ?>">Play the Galactic Lotto</a>
</div>
&nbsp;&nbsp;&nbsp;&nbsp;
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarBlackjackHREF(); ?>">Play Some Blackjack</a>
</div>

<br />
<br />

<h2>Ship</h2><br />
Well... of course you could always pay our painters to customise your ship name, or even spray on your favourite logo!<br />
<br />
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBuyShipnameHREF(); ?>">Customize Ship Name (<?php echo min(Globals::getBuyShipNameCosts()); ?>-<?php echo max(Globals::getBuyShipNameCosts()); ?> SMR Credits)</a>
</div>

<br />
<br />

<h2>Systems</h2><br />
We just got in a new system that can send information from your scout drones or recent news directly to your main screen! It only costs <?php echo CREDITS_PER_TICKER; ?> SMR credits for 5 days! Or you can buy a system to block these messages.<br />
<br />
<div class="buttonA">
	<a class="buttonA" href="<?php echo $BuySystemHREF; ?>">Buy System (<?php echo CREDITS_PER_TICKER; ?> SMR Credits)</a>
</div>
<br />
<br />

<h2>Maps</h2><br />
New intelligence has just come in!  We now have full maps of EVERY galaxy!  We are willing to sell you the newest maps of each galaxy for <?php echo CREDITS_PER_GAL_MAP; ?> SMR credits each!<br />
<br />
<div class="buttonA">
	<a class="buttonA" href="<?php echo $BuyGalMapHREF; ?>">Buy a Galaxy Map (<?php echo CREDITS_PER_GAL_MAP; ?> SMR Credits)</a>
</div><br /><br />
