<?php declare(strict_types=1);

/**
 * @var int $Amount
 * @var int $ToAccountID
 * @var string $HofName
 * @var string $SubmitHREF
 */

?>
<p>Are you sure you want to transfer <?php echo pluralise($Amount, 'SMR credit'); ?> to
account <?php echo $ToAccountID; ?> with Hall of Fame name <?php echo $HofName; ?>?</p>

<p class="bold">Please make sure this is definitely the correct person before confirming.</p>
<form method="POST" action="<?php echo $SubmitHREF; ?>">
	<input type="submit" name="action" value="Yes" />&nbsp;&nbsp;
	<input type="submit" name="action" value="No" />
</form>
