<?php echo $Message; ?>
<h2>Drinks</h2><br />
Wanna buy a drink? I got some good stuff! Just what you need after a hard day's hunting.<br /><br />

<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarBuyDrinkHREF(); ?>">&nbsp;Buy a drink ($10)&nbsp;</a>
</div>&nbsp;&nbsp;&nbsp;&nbsp;
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarBuyWaterHREF(); ?>">&nbsp;Buy some water ($10)&nbsp;</a>
</div>

<?php
//$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'bar_talk_bartender.php'));
//$PHP_OUTPUT.=create_submit('Talk to bartender');
//$PHP_OUTPUT.=('</form>');
?>

<br />
<br />
<h2>Gambling</h2><br />
So you're not the drinking type huh? Well how about some good ole gambling?<br />
<br />

<?php
if ($WinningTicket)
{ ?>
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
Well...of course you could always pay our painters to customize your ship name, or even spray on your favorite logo!<br />
<br />
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarShipnameHREF(); ?>">&nbsp;Customize Ship Name (1-3 SMR Credit(s))&nbsp;</a>
</div>

<br />
<br />

<h2>Systems</h2><br />
We just got in a new system that can send information from your scout drones or recent news directly to your main screen! It only costs 1 SMR credit for 5 days! Or you can buy a system to block these messages.<br />
<br />
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarTickerHREF(); ?>">&nbsp;Buy System (1 SMR Credit)&nbsp;</a>
</div>
<br />
<br />

<h2>Maps</h2><br />
New intelligence has just come in!  We now have full maps of EVERY galaxy!  We are willing to sell you the newest maps of each galaxy for 2 SMR credits each!<br />
<br />
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBarGalMapHREF(); ?>">&nbsp;Buy a Galaxy Map (2 SMR Credits)&nbsp;</a>
</div><br /><br />