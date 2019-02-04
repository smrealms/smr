<p>Are you sure you want to place a <span class="creds"><?php echo $Amount; ?></span>
credit and <span class="yellow"><?php echo $SmrCredits; ?></span>
SMR credit bounty on <?php echo $BountyPlayer; ?>?</p>

<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	<input type="submit" name="action" value="Yes" />
	&nbsp;&nbsp;
	<input type="submit" name="action" value="No" />
</form>
