<?php declare(strict_types=1);

/**
 * @var string $Amount
 * @var string $SmrCredits
 * @var string $BountyPlayer
 * @var string $ProcessingHREF
 */

?>
<p>Are you sure you want to place a <span class="creds"><?php echo $Amount; ?></span>
credit and <span class="yellow"><?php echo $SmrCredits; ?></span>
SMR credit bounty on <?php echo $BountyPlayer; ?>?</p>

<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	<?php echo create_submit('action', 'Yes'); ?>
	&nbsp;&nbsp;
	<?php echo create_submit('action', 'No'); ?>
</form>
