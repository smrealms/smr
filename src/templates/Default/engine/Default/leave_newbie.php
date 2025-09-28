<?php declare(strict_types=1);

/**
 * @var string $CancelHREF
 * @var string $ConfirmHREF
 */

?>

Do you really want to leave Newbie Protection?<br /><br />
<?php echo create_submit_link($ConfirmHREF, 'Yes!'); ?>&nbsp;&nbsp;
<?php echo create_submit_link($CancelHREF, 'No!');
