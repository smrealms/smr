Hello <?php echo $ThisPlayer->getPlayerName(); ?>
<br /><br />

Balance: <b><?php echo number_format($ThisPlayer->getBank()); ?></b>
<br /><br />
<h2>Make transaction</h2>
<br />

<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	Amount:&nbsp;<input type="number" name="amount" size="10" value="0"><br /><br />
	<input type="submit" name="action" value="Deposit" />
	&nbsp;&nbsp;
	<input type="submit" name="action" value="Withdraw" />
</form>
