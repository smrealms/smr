<?php declare(strict_types=1);

/**
 * @var string $Amount
 * @var string $SmrCredits
 * @var string $BountyPlayer
 * @var string $ConfirmHREF
 * @var string $CancelHREF
 */

?>
<p>Are you sure you want to place a <span class="creds"><?php echo $Amount; ?></span>
credit and <span class="yellow"><?php echo $SmrCredits; ?></span>
SMR credit bounty on <?php echo $BountyPlayer; ?>?</p>

<?php echo create_submit_link($ConfirmHREF, 'Yes'); ?>
&nbsp;&nbsp;
<?php echo create_submit_link($CancelHREF, 'No');
