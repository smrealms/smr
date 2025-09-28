<?php declare(strict_types=1);

/**
 * @var string $CancelHREF
 * @var string $ConfirmHREF
 * @var Smr\Game $Game
 */

?>
Are you sure you want to delete the game: <i><?php echo $Game->getDisplayName(); ?></i>?
<br /><br />

<?php echo create_submit_link($ConfirmHREF, 'Yes'); ?>
&nbsp;&nbsp;
<?php echo create_submit_link($CancelHREF, 'No');
