<?php declare(strict_types=1);

/**
 * @var string $CancelHref
 * @var string $ConfirmHref
 */

?>
Are you sure you want to delete your photo album entry and all comments added to it?<br />
This action cannot be undone.<br /><br />

<?php echo create_submit_link($ConfirmHref, 'Yes'); ?>&nbps;&nbsp;&nbsp;
<?php echo create_submit_link($CancelHref, 'No');
