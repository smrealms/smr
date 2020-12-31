<p>Are you sure you want to transfer <?php echo $Amount; ?> SMR credits to
the player with Hall of Fame name <?php echo $HofName; ?>?</p>

<p class="bold">Please make sure this is definitely the correct person before confirming.</p>
<form method="POST" action="<?php echo $SubmitHREF; ?>">
	<input type="submit" name="action" value="Yes" />&nbsp;&nbsp;
	<input type="submit" name="action" value="No" />
</form>
