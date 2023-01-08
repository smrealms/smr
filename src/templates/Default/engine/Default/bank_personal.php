<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 * @var string $ProcessingHREF
 */

?>
Hello <?php echo $ThisPlayer->getDisplayName(); ?>
<br /><br />

Balance: <b><?php echo number_format($ThisPlayer->getBank()); ?></b><?php
if ($ThisPlayer->getBank() >= MAX_MONEY) { ?>
	(Account is Full)<?php
} ?>
<br /><br />
<h2>Make transaction</h2>
<br />

<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	Amount:&nbsp;<input type="number" name="amount" min="1" required size="10"><br /><br />
	<input type="submit" name="action" value="Deposit" />
	&nbsp;&nbsp;
	<input type="submit" name="action" value="Withdraw" />
</form>
