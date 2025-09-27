<?php declare(strict_types=1);

/**
 * @var string $BuyHREF
 * @var array<string, int> $Tickers
 */

if (count($Tickers) > 0) { // to prevent docblock from applying to for-loop
	foreach ($Tickers as $Type => $TimeLeft) { ?>
		You own a <?php echo $Type; ?> for another <?php echo format_time($TimeLeft); ?>.
		<br /><?php
	}
} ?>

<br />
Great idea!  So what do you want us to configure your system to do?<br />
<form method="POST" action="<?php echo $BuyHREF; ?>">
	<input type="radio" name="type" required value="SCOUT" />Send Scout Messages<br />
	<input type="radio" name="type" required value="NEWS" />Send Recent News<br />
	<input type="radio" name="type" required value="BLOCK" />Block Scout Message Tickers<br />
	<small>This will only block messages to tickers, it will not completely block scout messages</small><br />
	<br />
	<?php echo create_submit('action', 'Buy'); ?>
</form>
