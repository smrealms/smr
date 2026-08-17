<?php declare(strict_types=1);

namespace Smr\Pages\Player;

class NewbieLeaveRenderer {

public static function render(string $CancelHREF, string $ConfirmHREF): void {
?>
Do you really want to leave Newbie Protection?<br /><br />
<?php echo create_submit_link($ConfirmHREF, 'Yes!'); ?>&nbsp;&nbsp;
<?php echo create_submit_link($CancelHREF, 'No!');
}

}
