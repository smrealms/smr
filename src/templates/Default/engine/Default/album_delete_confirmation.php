<?php declare(strict_types=1);

/**
 * @var string $ConfirmAlbumDeleteHref
 */

?>
Are you sure you want to delete your photo album entry and all comments added to it?<br />
This action cannot be undone.<br /><br />

<form name="ConfirmAlbumDeleteForm" method="POST" action="<?php echo $ConfirmAlbumDeleteHref; ?>">
	<input type="submit" name="action" value="Yes" />&nbsp;&nbsp;&nbsp;<input type="submit" name="action" value="No" />
</form>
