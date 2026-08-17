<?php declare(strict_types=1);

namespace Smr\Pages\Account;

class AlbumDeleteConfirmRenderer {

public static function render(string $CancelHref, string $ConfirmHref): void {
?>
Are you sure you want to delete your photo album entry and all comments added to it?<br />
This action cannot be undone.<br /><br />

<?php echo create_submit_link($ConfirmHref, 'Yes'); ?>&nbsp;&nbsp;&nbsp;
<?php echo create_submit_link($CancelHref, 'No');
}

}
