<?php declare(strict_types=1);

/**
 * @var string $ConfirmAlbumDeleteHref
 */

?>
Are you sure you want to delete your photo album entry and all comments added to it?<br />
This action cannot be undone.<br /><br />

<form name="ConfirmAlbumDeleteForm" method="POST" action="<?php echo $ConfirmAlbumDeleteHref; ?>">
	<?php echo create_submit('action', 'Yes'); ?>&nbps;&nbsp;&nbsp;<?php echo create_submit('action', 'No'); ?>
</form>
