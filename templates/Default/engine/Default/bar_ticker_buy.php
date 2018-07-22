<?php
foreach ($Tickers as $Type => $TimeLeft) { ?>
	You own a <?php echo $Type; ?> for another <?php echo format_time($TimeLeft); ?>.
	<br /><?php
} ?>

<br />
Great idea!  So what do you want us to configure your system to do?<br />
<form method="POST" action="<?php echo $BuyHREF; ?>">
	<input type="radio" name="type" value="SCOUT" />Send Scout Messages<br />
	<input type="radio" name="type" value="NEWS" />Send Recent News<br />
	<input type="radio" name="type" value="BLOCK" />Block Scout Message Tickers<br />
	<small>This will only block messages to tickers, it will not completely block scout messages</small><br />
	<br />
	<input type="submit" name="action" value="Buy" />
</form>
